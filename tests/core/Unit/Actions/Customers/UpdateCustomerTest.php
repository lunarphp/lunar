<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Actions\Customers\UpdateCustomer;
use Lunar\Core\Models\Customer;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

test('updates the customer attributes', function () {
    $customer = Customer::factory()->create([
        'first_name' => 'Tony',
        'last_name' => 'Stark',
    ]);

    $updated = app(UpdateCustomer::class)->execute($customer, [
        'first_name' => 'Peter',
        'last_name' => 'Parker',
    ]);

    expect($updated->id)->toBe($customer->id);

    $this->assertDatabaseHas('lunar_customers', [
        'id' => $customer->id,
        'first_name' => 'Peter',
        'last_name' => 'Parker',
    ]);
});

test('syncs customer groups to the given set', function () {
    $customer = Customer::factory()->create();
    $existingGroup = CustomerGroup::factory()->create();
    $customer->customerGroups()->attach($existingGroup);

    $newGroups = CustomerGroup::factory(2)->create();

    app(UpdateCustomer::class)->execute($customer, [], $newGroups->pluck('id')->all());

    $customer->refresh();

    expect($customer->customerGroups()->get())->toHaveCount(2);
    $this->assertDatabaseMissing('lunar_customer_customer_group', [
        'customer_id' => $customer->id,
        'customer_group_id' => $existingGroup->id,
    ]);
});

test('clears customer groups when an empty set is given', function () {
    $customer = Customer::factory()->create();
    $group = CustomerGroup::factory()->create();
    $customer->customerGroups()->attach($group);

    app(UpdateCustomer::class)->execute($customer, []);

    expect($customer->customerGroups()->get())->toHaveCount(0);
});
