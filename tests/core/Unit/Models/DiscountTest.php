<?php

uses(\Lunar\Tests\Core\TestCase::class);
use Lunar\Models\Discount;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('can apply usable scope', function () {
    Discount::factory()->create([
        'max_uses' => null,
    ]);

    Discount::factory()->create([
        'uses' => 10,
        'max_uses' => 11,
    ]);

    $discountC = Discount::factory()->create([
        'uses' => 10,
        'max_uses' => 10,
    ]);

    $discounts = Discount::usable()->get();

    expect($discounts)->toHaveCount(2);
    expect($discounts->first(
        fn ($discount) => $discount->id == $discountC->id
    ))->toBeNull();
});
