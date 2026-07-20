<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Actions\CustomerGroups\UpdateCustomerGroup;
use Lunar\Core\Exceptions\CustomerGroupActionException;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

test('updates the customer group attributes', function () {
    $group = CustomerGroup::factory()->create(['name' => 'Old Name', 'default' => false]);

    app(UpdateCustomerGroup::class)->execute($group, ['name' => 'New Name']);

    $this->assertDatabaseHas('lunar_customer_groups', [
        'id' => $group->id,
        'name' => 'New Name',
    ]);
});

test('promoting to default demotes the previous default', function () {
    $previous = CustomerGroup::factory()->create(['default' => true]);
    $group = CustomerGroup::factory()->create(['default' => false]);

    app(UpdateCustomerGroup::class)->execute($group, ['default' => true]);

    expect($previous->refresh()->default)->toBeFalse()
        ->and($group->refresh()->default)->toBeTrue();
});

test('refuses to unset the default flag directly', function () {
    $group = CustomerGroup::factory()->create(['default' => true]);

    expect(fn () => app(UpdateCustomerGroup::class)->execute($group, ['default' => false]))
        ->toThrow(CustomerGroupActionException::class);
});
