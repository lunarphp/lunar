<?php

uses(\Lunar\Tests\Core\TestCase::class);
uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

use Illuminate\Support\Facades\Config;
use Lunar\Actions\Customers\DeleteCustomer;
use Lunar\Models\Address;
use Lunar\Models\Cart;
use Lunar\Models\Customer;
use Lunar\Models\CustomerGroup;
use Lunar\Tests\Core\Stubs\User;

test('can delete customer completely', function () {
    $customer = Customer::factory()->create();
    $customerId = $customer->id;

    $action = new DeleteCustomer;
    $action->execute($customer);

    $this->assertDatabaseMissing('lunar_customers', ['id' => $customerId]);
});

test('deletes customer carts and cart lines', function () {
    $customer = Customer::factory()->create();
    $cart = Cart::factory()->create(['customer_id' => $customer->id]);

    $action = new DeleteCustomer;
    $action->execute($customer);

    // Cart should be deleted
    $this->assertDatabaseMissing('lunar_carts', ['id' => $cart->id]);
});

test('deletes customer addresses', function () {
    $customer = Customer::factory()->create();
    $address = Address::factory()->create(['customer_id' => $customer->id]);

    $action = new DeleteCustomer;
    $action->execute($customer);

    // Address should be deleted
    $this->assertDatabaseMissing('lunar_addresses', ['id' => $address->id]);
});

test('detaches customer groups and discounts before deletion', function () {
    $customer = Customer::factory()->create();
    $group = CustomerGroup::factory()->create();

    $customer->customerGroups()->attach($group);

    $action = new DeleteCustomer;
    $action->execute($customer);

    // Customer should be deleted but group should remain
    $this->assertDatabaseMissing('lunar_customers', ['id' => $customer->id]);
    $this->assertDatabaseHas('lunar_customer_groups', ['id' => $group->id]);
});

test('handles orphaned users when configured to delete them', function () {
    Config::set('lunar.customers.delete_orphaned_users', true);

    $user = User::factory()->create();
    $customer = Customer::factory()->create();

    $customer->users()->attach($user);

    $action = new DeleteCustomer;
    $action->execute($customer);

    // Both customer and user should be deleted
    $this->assertDatabaseMissing('lunar_customers', ['id' => $customer->id]);
    $this->assertDatabaseMissing('users', ['id' => $user->id]);
});

test('preserves users when they have multiple customers', function () {
    Config::set('lunar.customers.delete_orphaned_users', true);

    $user = User::factory()->create();
    $customer1 = Customer::factory()->create();
    $customer2 = Customer::factory()->create();

    $customer1->users()->attach($user);
    $customer2->users()->attach($user);

    $action = new DeleteCustomer;
    $action->execute($customer1);

    // Customer1 should be deleted but user should remain
    $this->assertDatabaseMissing('lunar_customers', ['id' => $customer1->id]);
    $this->assertDatabaseHas('users', ['id' => $user->id]);

    // User should still be linked to customer2
    expect($customer2->users()->count())->toBe(1);
});

test('only detaches users when configured not to delete orphaned users', function () {
    Config::set('lunar.customers.delete_orphaned_users', false);

    $user = User::factory()->create();
    $customer = Customer::factory()->create();

    $customer->users()->attach($user);

    $action = new DeleteCustomer;
    $action->execute($customer);

    // Customer should be deleted but user should be preserved
    $this->assertDatabaseMissing('lunar_customers', ['id' => $customer->id]);
    $this->assertDatabaseHas('users', ['id' => $user->id]);
});
