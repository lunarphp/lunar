<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Actions\Discounts\CreateDiscount;
use Lunar\Core\DiscountTypes\PercentageOff;
use Lunar\Core\Models\Discount;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

test('creates a discount with the given attributes', function () {
    $discount = app(CreateDiscount::class)->execute([
        'name' => 'Winter Sale',
        'handle' => 'winter-sale',
        'type' => PercentageOff::class,
        'coupon' => 'WINTER',
        'starts_at' => now(),
        'data' => ['percentage' => 15],
    ]);

    expect($discount->exists)->toBeTrue();
    expect(Discount::whereHandle('winter-sale')->count())->toBe(1);
    expect($discount->name)->toBe('Winter Sale');
    expect($discount->type)->toBe(PercentageOff::class);
    expect($discount->coupon)->toBe('WINTER');
    expect($discount->data)->toBe(['percentage' => 15]);
});
