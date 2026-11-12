<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Enums\DiscountRedemptionMethod;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\Coupon;
use Lunar\Core\Models\Discount;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

test('a code is stored uppercase', function () {
    expect(Coupon::factory()->create(['code' => 'spring10'])->refresh()->code)->toBe('SPRING10');
});

test('a coupon is usable until it reaches its limit', function () {
    $unlimited = Coupon::factory()->create(['uses' => 50]);
    $spare = Coupon::factory()->create(['uses' => 1, 'max_uses' => 2]);
    $spent = Coupon::factory()->create(['uses' => 2, 'max_uses' => 2]);

    expect(Coupon::query()->usable()->pluck('id')->sort()->values()->all())->toBe([$unlimited->id, $spare->id])
        ->and($unlimited->isUsable())->toBeTrue()
        ->and($spare->isUsable())->toBeTrue()
        ->and($spent->isUsable())->toBeFalse();
});

test('a discount is automatic unless it needs a coupon', function () {
    expect((new Discount)->redemption)->toBe(DiscountRedemptionMethod::Automatic)
        ->and(Discount::factory()->coupon('WINTER')->create()->coupons()->sole()->code)->toBe('WINTER');
});

test('a cart resolves its code to a coupon, whatever its case', function () {
    $coupon = Coupon::factory()->create(['code' => 'SPRING10']);

    $cart = Cart::factory()->create(['coupon_code' => 'spring10']);

    expect($cart->coupon->is($coupon))->toBeTrue();

    $cart->coupon_code = 'OTHER';

    expect($cart->relationLoaded('coupon'))->toBeFalse()
        ->and($cart->coupon)->toBeNull();
});

test('deleting a discount deletes its coupons', function () {
    $discount = Discount::factory()->coupon('WINTER')->create();

    $discount->delete();

    expect(Coupon::query()->count())->toBe(0);
});
