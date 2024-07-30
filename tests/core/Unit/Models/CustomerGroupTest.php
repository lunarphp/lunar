<?php

uses(\Lunar\Tests\Core\TestCase::class);

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('can return discounts', function () {
    $customerGroup = \Lunar\Models\CustomerGroup::factory()->create();

    $discount = \Lunar\Models\Discount::factory()->create();

    expect($customerGroup->discounts)->toHaveCount(0);

    $customerGroup->discounts()->attach($discount->id);

    expect($customerGroup->refresh()->discounts)->toHaveCount(1);
});
