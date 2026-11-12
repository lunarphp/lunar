<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Contracts\Actions\Discounts\CreatesCoupon;
use Lunar\Core\Contracts\Actions\Discounts\DeletesCoupon;
use Lunar\Core\Models\Discount;
use Lunar\Core\Models\DiscountRedemption;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

test('adds a code to a discount', function () {
    $discount = Discount::factory()->coupon()->create();

    $coupon = app(CreatesCoupon::class)->execute($discount, ['code' => 'spring10', 'max_uses' => 5]);

    expect($coupon->discount->is($discount))->toBeTrue()
        ->and($coupon->refresh()->code)->toBe('SPRING10')
        ->and($coupon->max_uses)->toBe(5)
        ->and($coupon->uses)->toBe(0);
});

test('deleting a code keeps its redemptions', function () {
    $discount = Discount::factory()->coupon('SPRING10')->create();
    $coupon = $discount->coupons()->sole();
    $redemption = DiscountRedemption::factory()->for($discount)->create(['coupon_id' => $coupon->id]);

    app(DeletesCoupon::class)->execute($coupon);

    expect($discount->coupons()->count())->toBe(0)
        ->and($redemption->refresh()->coupon_id)->toBeNull();
});
