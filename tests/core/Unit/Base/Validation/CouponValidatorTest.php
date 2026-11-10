<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Base\Validation\CouponValidator;
use Lunar\DiscountTypes\AmountOff;
use Lunar\Models\Discount;
use Lunar\Tests\Core\Stubs\User;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);

uses(RefreshDatabase::class);

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

test('enforces max uses per user for the authenticated user', function () {
    setAuthUserConfig();

    $validator = app(CouponValidator::class);

    $user = User::factory()->create();

    $discount = Discount::factory()->create([
        'type' => AmountOff::class,
        'name' => 'Test Coupon',
        'max_uses_per_user' => 1,
        'coupon' => '10OFF',
        'data' => [
            'fixed_value' => false,
            'percentage' => 10,
        ],
    ]);

    $this->actingAs($user);

    expect($validator->validate('10OFF'))->toBeTrue();

    $discount->users()->attach($user->id);

    expect($validator->validate('10OFF'))->toBeFalse();
});

test('does not enforce max uses per user for a guest', function () {
    setAuthUserConfig();

    $validator = app(CouponValidator::class);

    $user = User::factory()->create();

    $discount = Discount::factory()->create([
        'type' => AmountOff::class,
        'name' => 'Test Coupon',
        'max_uses_per_user' => 1,
        'coupon' => '10OFF',
        'data' => [
            'fixed_value' => false,
            'percentage' => 10,
        ],
    ]);

    $discount->users()->attach($user->id);

    expect($validator->validate('10OFF'))->toBeTrue();
});

test('allows another user when one is at the per-user limit', function () {
    setAuthUserConfig();

    $validator = app(CouponValidator::class);

    $userA = User::factory()->create();
    $userB = User::factory()->create();

    $discount = Discount::factory()->create([
        'type' => AmountOff::class,
        'name' => 'Test Coupon',
        'max_uses_per_user' => 1,
        'coupon' => '10OFF',
        'data' => [
            'fixed_value' => false,
            'percentage' => 10,
        ],
    ]);

    $discount->users()->attach($userA->id);

    $this->actingAs($userB);

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
