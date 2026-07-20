<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Actions\CustomerGroups\CreateCustomerGroup;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

test('creates a customer group with the given attributes', function () {
    $group = app(CreateCustomerGroup::class)->execute([
        'name' => 'Trade',
        'handle' => 'trade',
    ]);

    expect($group)->toBeInstanceOf(CustomerGroup::class);

    $this->assertDatabaseHas('lunar_customer_groups', [
        'id' => $group->id,
        'name' => 'Trade',
        'handle' => 'trade',
    ]);
});

test('demotes the previous default when created as default', function () {
    $previous = CustomerGroup::factory()->create(['default' => true]);

    $group = app(CreateCustomerGroup::class)->execute([
        'name' => 'Trade',
        'handle' => 'trade',
        'default' => true,
    ]);

    expect($previous->refresh()->default)->toBeFalse()
        ->and($group->refresh()->default)->toBeTrue();
});
