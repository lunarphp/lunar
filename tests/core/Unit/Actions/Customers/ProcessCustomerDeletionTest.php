<?php

uses(\Lunar\Tests\Core\TestCase::class);
uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

use Illuminate\Support\Facades\Config;
use Lunar\Actions\Customers\ProcessCustomerDeletion;
use Lunar\Models\Address;
use Lunar\Models\Cart;
use Lunar\Models\Customer;
use Lunar\Models\CustomerGroup;
use Lunar\Tests\Core\Stubs\User;

test('can anonymize customer with default strategy', function () {
    Config::set('lunar.customers.deletion_strategy', 'anonymize');

    $customer = Customer::factory()->create([
        'first_name' => 'John',
        'last_name' => 'Doe',
        'company_name' => 'Acme Corp',
    ]);

    ProcessCustomerDeletion::run($customer);

    $customer->refresh();

    expect($customer->first_name)->toBe('Anonymous');
    expect($customer->last_name)->toBe('Customer');
    expect($customer->company_name)->toBeNull();
    expect($customer->meta['anonymized'])->toBeTrue();
});

test('can delete customer completely with delete strategy', function () {
    Config::set('lunar.customers.deletion_strategy', 'delete');

    $customer = Customer::factory()->create();
    $customerId = $customer->id;

    ProcessCustomerDeletion::run($customer);

    $this->assertDatabaseMissing('lunar_customers', ['id' => $customerId]);
});

test('can override default strategy', function () {
    Config::set('lunar.customers.deletion_strategy', 'anonymize');

    $customer = Customer::factory()->create();
    $customerId = $customer->id;

    ProcessCustomerDeletion::run($customer, 'delete');

    $this->assertDatabaseMissing('lunar_customers', ['id' => $customerId]);
});

test('handles customer with carts correctly during anonymization', function () {
    Config::set('lunar.customers.deletion_strategy', 'anonymize');

    $customer = Customer::factory()->create();
    $cart = Cart::factory()->create(['customer_id' => $customer->id]);

    ProcessCustomerDeletion::run($customer);

    $customer->refresh();
    expect($customer->first_name)->toBe('Anonymous');

    // Cart should be deleted by default
    $this->assertDatabaseMissing('lunar_carts', ['id' => $cart->id]);
});

test('handles orphaned users correctly when configured to delete them', function () {
    Config::set('lunar.customers.deletion_strategy', 'anonymize');
    Config::set('lunar.customers.delete_orphaned_users', true);

    $user = User::factory()->create();
    $customer = Customer::factory()->create();

    $customer->users()->attach($user);

    ProcessCustomerDeletion::run($customer);

    // User should be deleted as it's orphaned
    $this->assertDatabaseMissing('users', ['id' => $user->id]);
});

test('preserves users when they have multiple customers', function () {
    Config::set('lunar.customers.deletion_strategy', 'anonymize');
    Config::set('lunar.customers.delete_orphaned_users', true);

    $user = User::factory()->create();
    $customer1 = Customer::factory()->create();
    $customer2 = Customer::factory()->create();

    $customer1->users()->attach($user);
    $customer2->users()->attach($user);

    ProcessCustomerDeletion::run($customer1);

    // User should still exist as it's linked to customer2
    $this->assertDatabaseHas('users', ['id' => $user->id]);

    // But relationship should be detached from customer1
    expect($customer1->users()->count())->toBe(0);
    expect($customer2->users()->count())->toBe(1);
});

test('preserves users when configured not to delete orphaned users', function () {
    Config::set('lunar.customers.deletion_strategy', 'anonymize');
    Config::set('lunar.customers.delete_orphaned_users', false);

    $user = User::factory()->create();
    $customer = Customer::factory()->create();

    $customer->users()->attach($user);

    ProcessCustomerDeletion::run($customer);

    // User should be preserved even if orphaned
    $this->assertDatabaseHas('users', ['id' => $user->id]);

    // But relationship should be detached
    expect($customer->users()->count())->toBe(0);
});

test('handles customer groups and discounts correctly', function () {
    $customer = Customer::factory()->create();
    $group = CustomerGroup::factory()->create();

    $customer->customerGroups()->attach($group);

    ProcessCustomerDeletion::run($customer);

    // Relationship should be detached
    expect($customer->customerGroups()->count())->toBe(0);

    // Customer group should still exist
    $this->assertDatabaseHas('lunar_customer_groups', ['id' => $group->id]);
});

test('handles addresses correctly during anonymization', function () {
    Config::set('lunar.customers.deletion_strategy', 'anonymize');

    $customer = Customer::factory()->create();
    $address = Address::factory()->create([
        'customer_id' => $customer->id,
        'first_name' => 'John',
        'line_one' => '123 Real Street',
    ]);

    ProcessCustomerDeletion::run($customer);

    $address->refresh();
    expect($address->first_name)->toBe('Anonymous');
    expect($address->line_one)->toBe('Anonymized');
    expect($address->meta['anonymized'])->toBeTrue();
});

test('handles addresses correctly during deletion', function () {
    Config::set('lunar.customers.deletion_strategy', 'delete');

    $customer = Customer::factory()->create();
    $address = Address::factory()->create([
        'customer_id' => $customer->id,
    ]);

    ProcessCustomerDeletion::run($customer);

    // Address should be deleted
    $this->assertDatabaseMissing('lunar_addresses', ['id' => $address->id]);
});

test('uses configured anonymization fields', function () {
    Config::set('lunar.customers.deletion_strategy', 'anonymize');
    Config::set('lunar.customers.anonymization_fields', [
        'first_name' => 'Test User',
        'last_name' => 'Anonymous',
        'company_name' => 'Company :id',
    ]);

    $customer = Customer::factory()->create([
        'first_name' => 'John',
        'last_name' => 'Doe',
        'company_name' => 'Acme Corp',
    ]);

    ProcessCustomerDeletion::run($customer);

    $customer->refresh();

    expect($customer->first_name)->toBe('Test User');
    expect($customer->last_name)->toBe('Anonymous');
    expect($customer->company_name)->toBe("Company customer_{$customer->id}");
});
