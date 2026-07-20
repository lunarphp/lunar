<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Actions\CustomerGroups\DeleteCustomerGroup;
use Lunar\Core\Exceptions\CustomerGroupActionException;
use Lunar\Core\Models\Customer;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

test('deletes a customer group without customers', function () {
    $group = CustomerGroup::factory()->create(['default' => false]);

    app(DeleteCustomerGroup::class)->execute($group);

    $this->assertDatabaseMissing('lunar_customer_groups', ['id' => $group->id]);
});

test('refuses to delete the default customer group', function () {
    $group = CustomerGroup::factory()->create(['default' => true]);

    expect(fn () => app(DeleteCustomerGroup::class)->execute($group))
        ->toThrow(CustomerGroupActionException::class);
});

test('refuses to delete a customer group with customers', function () {
    $group = CustomerGroup::factory()->create(['default' => false]);
    $group->customers()->attach(Customer::factory()->create());

    expect(fn () => app(DeleteCustomerGroup::class)->execute($group))
        ->toThrow(CustomerGroupActionException::class);

    $this->assertDatabaseHas('lunar_customer_groups', ['id' => $group->id]);
});
