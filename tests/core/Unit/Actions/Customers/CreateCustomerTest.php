<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Actions\Customers\CreateCustomer;
use Lunar\Core\Models\Customer;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

test('creates a customer with the given attributes', function () {
    $customer = app(CreateCustomer::class)->execute([
        'first_name' => 'Tony',
        'last_name' => 'Stark',
        'company_name' => 'Stark Industries',
    ]);

    expect($customer)->toBeInstanceOf(Customer::class);

    $this->assertDatabaseHas('lunar_customers', [
        'id' => $customer->id,
        'first_name' => 'Tony',
        'last_name' => 'Stark',
        'company_name' => 'Stark Industries',
    ]);
});

test('syncs the given customer groups', function () {
    $groups = CustomerGroup::factory(2)->create();

    $customer = app(CreateCustomer::class)->execute([
        'first_name' => 'Tony',
        'last_name' => 'Stark',
    ], $groups->pluck('id')->all());

    expect($customer->customerGroups()->get())->toHaveCount(2);

    foreach ($groups as $group) {
        $this->assertDatabaseHas('lunar_customer_customer_group', [
            'customer_id' => $customer->id,
            'customer_group_id' => $group->id,
        ]);
    }
});

test('does not touch groups when none are given', function () {
    $customer = app(CreateCustomer::class)->execute([
        'first_name' => 'Tony',
        'last_name' => 'Stark',
    ]);

    expect($customer->customerGroups()->get())->toHaveCount(0);
});
