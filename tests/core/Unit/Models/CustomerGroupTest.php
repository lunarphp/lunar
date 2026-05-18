<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Models\CustomerGroup;
use Lunar\Models\Discount;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);

uses(RefreshDatabase::class);

test('can return discounts', function () {
    $customerGroup = CustomerGroup::factory()->create();

    Discount::factory()->create();

    expect($customerGroup->refresh()->discounts)->toHaveCount(1);
});
