<?php

uses(\Lunar\Tests\Core\TestCase::class);
uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

use Illuminate\Support\Facades\Config;
use Lunar\Actions\Customers\AnonymizeCustomer;
use Lunar\Models\Address;
use Lunar\Models\Cart;
use Lunar\Models\Customer;
use Lunar\Models\CustomerGroup;
use Lunar\Tests\Core\Stubs\User;

test('can anonymize customer data', function () {
    $customer = Customer::factory()->create([
        'first_name' => 'John',
        'last_name' => 'Doe',
        'company_name' => 'Acme Corp',
        'tax_identifier' => '123456789',
        'account_ref' => 'CUST001',
    ]);

    $action = new AnonymizeCustomer;
    $action->execute($customer);

    $customer->refresh();

    expect($customer->first_name)->toBe('Anonymous');
    expect($customer->last_name)->toBe('Customer');
    expect($customer->company_name)->toBeNull();
    expect($customer->tax_identifier)->toBeNull();
    expect($customer->account_ref)->toBeNull();
    expect($customer->meta['anonymized'])->toBeTrue();
    expect($customer->meta['anonymized_at'])->not->toBeNull();
});

test('can delete customer carts during anonymization', function () {
    Config::set('lunar.customers.cart_handling', 'delete');

    $customer = Customer::factory()->create();
    $cart = Cart::factory()->create(['customer_id' => $customer->id]);

    $action = new AnonymizeCustomer;
    $action->execute($customer);

    // Cart should be deleted
    $this->assertDatabaseMissing('lunar_carts', ['id' => $cart->id]);
});

test('can anonymize customer carts instead of deleting', function () {
    Config::set('lunar.customers.cart_handling', 'anonymize');

    $customer = Customer::factory()->create();
    $cart = Cart::factory()->create(['customer_id' => $customer->id]);

    $action = new AnonymizeCustomer;
    $action->execute($customer);

    $cart->refresh();

    // Cart should exist but customer_id should be null
    $this->assertDatabaseHas('lunar_carts', ['id' => $cart->id]);
    expect($cart->customer_id)->toBeNull();
});

test('detaches customer groups and discounts', function () {
    $customer = Customer::factory()->create();
    $group = CustomerGroup::factory()->create();

    $customer->customerGroups()->attach($group);

    $action = new AnonymizeCustomer;
    $action->execute($customer);

    expect($customer->customerGroups()->count())->toBe(0);
});

test('anonymizes customer addresses', function () {
    $customer = Customer::factory()->create();
    $address = Address::factory()->create([
        'customer_id' => $customer->id,
        'first_name' => 'John',
        'last_name' => 'Doe',
        'line_one' => '123 Real Street',
        'city' => 'Real City',
        'postcode' => '12345',
    ]);

    $action = new AnonymizeCustomer;
    $action->execute($customer);

    $address->refresh();

    expect($address->first_name)->toBe('Anonymous');
    expect($address->last_name)->toBe('Customer');
    expect($address->line_one)->toBe('Anonymized');
    expect($address->city)->toBe('Anonymized');
    expect($address->postcode)->toBe('00000');
    expect($address->meta['anonymized'])->toBeTrue();
});

test('handles orphaned users when configured to delete them', function () {
    Config::set('lunar.customers.delete_orphaned_users', true);

    $user = User::factory()->create();
    $customer = Customer::factory()->create();

    $customer->users()->attach($user);

    $action = new AnonymizeCustomer;
    $action->execute($customer);

    // User should be deleted as it's orphaned
    $this->assertDatabaseMissing('users', ['id' => $user->id]);
});

test('preserves users when they are not orphaned', function () {
    Config::set('lunar.customers.delete_orphaned_users', true);

    $user = User::factory()->create();
    $customer1 = Customer::factory()->create();
    $customer2 = Customer::factory()->create();

    $customer1->users()->attach($user);
    $customer2->users()->attach($user);

    $action = new AnonymizeCustomer;
    $action->execute($customer1);

    // User should still exist
    $this->assertDatabaseHas('users', ['id' => $user->id]);

    // User should only be detached from customer1
    expect($customer1->users()->count())->toBe(0);
    expect($customer2->users()->count())->toBe(1);
});

test('only detaches users when configured not to delete orphaned users', function () {
    Config::set('lunar.customers.delete_orphaned_users', false);

    $user = User::factory()->create();
    $customer = Customer::factory()->create();

    $customer->users()->attach($user);

    $action = new AnonymizeCustomer;
    $action->execute($customer);

    // User should be preserved
    $this->assertDatabaseHas('users', ['id' => $user->id]);

    // But relationship should be detached
    expect($customer->users()->count())->toBe(0);
});
