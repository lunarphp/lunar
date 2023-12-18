<?php

uses(\Lunar\Tests\Core\TestCase::class);
use Lunar\Base\Validation\CouponValidator;
use Lunar\DiscountTypes\AmountOff;
use Lunar\Models\Discount;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('can validate coupons', function () {
    $validator = app(CouponValidator::class);

    Discount::factory()->create([
        'type' => AmountOff::class,
        'name' => 'Test Coupon',
        'coupon' => '10OFF',
        'data' => [
            'fixed_value' => false,
            'percentage' => 10,
        ],
    ]);

    expect($validator->validate('10OFF'))->toBeTrue();

    expect($validator->validate('10off'))->toBeTrue();

    expect($validator->validate('10oFf'))->toBeTrue();

    expect($validator->validate('20OFF'))->toBeFalse();
});

test('can validate based on uses', function () {
    $validator = app(CouponValidator::class);

    $discount = Discount::factory()->create([
        'type' => AmountOff::class,
        'name' => 'Test Coupon',
        'uses' => 10,
        'max_uses' => 20,
        'coupon' => '10OFF',
        'data' => [
            'fixed_value' => false,
            'percentage' => 10,
        ],
    ]);

    expect($validator->validate('10OFF'))->toBeTrue();

    $discount->update([
        'uses' => 20,
    ]);

    expect($validator->validate('10OFF'))->toBeFalse();

    $discount->update([
        'max_uses' => null,
    ]);

    expect($validator->validate('10OFF'))->toBeTrue();
});

test('can validate based on start and end dates', function () {
    $validator = app(CouponValidator::class);

    $discount = Discount::factory()->create([
        'type' => AmountOff::class,
        'name' => 'Test Coupon',
        'uses' => 0,
        'max_uses' => null,
        'starts_at' => now()->startOfDay(),
        'ends_at' => now()->endOfWeek(),
        'coupon' => '10OFF',
        'data' => [
            'fixed_value' => false,
            'percentage' => 10,
        ],
    ]);

    expect($validator->validate('10OFF'))->toBeTrue();

    $discount->update([
        'starts_at' => now()->subWeek(),
        'ends_at' => now()->subWeek()->endOfWeek(),
    ]);

    expect($validator->validate('10OFF'))->toBeFalse();

    $discount->update([
        'starts_at' => now()->subWeek(),
        'ends_at' => now()->subWeek()->endOfWeek(),
    ]);

    expect($validator->validate('10OFF'))->toBeFalse();
});
