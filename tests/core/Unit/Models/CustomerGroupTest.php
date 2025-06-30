<?php

uses(\Lunar\Tests\Core\TestCase::class);

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('can return discounts', function () {
    $customerGroup = \Lunar\Models\CustomerGroup::factory()->create();

    \Lunar\Models\Discount::factory()->create();

    expect($customerGroup->refresh()->discounts)->toHaveCount(1);
});
