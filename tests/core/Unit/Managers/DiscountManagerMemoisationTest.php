<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\DiscountTypes\AmountOff;
use Lunar\Facades\Discounts;
use Lunar\Models\Cart;
use Lunar\Models\Channel;
use Lunar\Models\Currency;
use Lunar\Models\CustomerGroup;
use Lunar\Models\Discount;
use Lunar\Models\Price;
use Lunar\Models\ProductVariant;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);

uses(RefreshDatabase::class);

/**
 * `DiscountManager::apply()` memoises the applicable-discount set on the first
 * call of a request. `getDiscounts()` filters on the cart's `coupon_code`, so a
 * set built while the cart had no coupon cannot contain a coupon discount — and
 * a coupon applied afterwards has no effect.
 *
 * `can get discount with coupon` in DiscountManagerTest does not cover this: it
 * calls `Discounts::getDiscounts()` directly, which bypasses `apply()` and
 * therefore the memoisation. These go through the cart pipeline instead.
 */
function discountMemoFixture(): Cart
{
    $currency = Currency::factory()->create([
        'code' => 'GBP',
        'default' => true,
    ]);

    $customerGroup = CustomerGroup::factory()->create(['default' => true]);
    $channel = Channel::factory()->create(['default' => true]);

    $variant = ProductVariant::factory()->create();

    Price::factory()->create([
        'price' => 1000,
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => $variant->getMorphClass(),
        'priceable_id' => $variant->id,
    ]);

    $attach = function (Discount $discount) use ($channel, $customerGroup) {
        $discount->channels()->attach([
            $channel->id => ['enabled' => true, 'starts_at' => now()->subDay()],
        ]);

        $discount->customerGroups()->attach([
            $customerGroup->id => ['enabled' => true, 'starts_at' => now()->subDay()],
        ]);
    };

    // An always-on discount is required to reproduce this: apply() re-queries
    // whenever the memoised set is empty, so the staleness only shows once the
    // first calculation already found something.
    $attach(Discount::factory()->create([
        'type' => AmountOff::class,
        'name' => 'Always on',
        'coupon' => null,
        'starts_at' => now()->subDay(),
        'data' => ['fixed_value' => true, 'fixed_values' => ['GBP' => 1]],
    ]));

    $attach(Discount::factory()->create([
        'type' => AmountOff::class,
        'name' => 'Coupon discount',
        'coupon' => 'SAVE',
        'starts_at' => now()->subDay(),
        'data' => ['fixed_value' => true, 'fixed_values' => ['GBP' => 5]],
    ]));

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
        'channel_id' => $channel->id,
        'coupon_code' => null,
    ]);

    $cart->lines()->create([
        'purchasable_type' => $variant->getMorphClass(),
        'purchasable_id' => $variant->id,
        'quantity' => 2,
    ]);

    return $cart->refresh();
}

test('a coupon applied after the cart has been calculated is applied', function () {
    $cart = discountMemoFixture();

    // The storefront renders the cart, which calculates it. The cart has no
    // coupon yet, so the memoised set cannot contain the coupon discount.
    $cart->recalculate();

    $withoutCoupon = $cart->total->value;

    $cart->coupon_code = 'SAVE';
    $cart->save();
    $cart->recalculate();

    expect($cart->coupon_code)->toBe('SAVE')
        ->and($cart->total->value)->toBeLessThan($withoutCoupon);
});

test('resetting the discount manager applies the same coupon', function () {
    $cart = discountMemoFixture();

    $cart->recalculate();
    $withoutCoupon = $cart->total->value;

    $cart->coupon_code = 'SAVE';
    $cart->save();

    // Same cart, same coupon, only the memoised set dropped.
    Discounts::resetDiscounts();
    $cart->recalculate();

    expect($cart->total->value)->toBeLessThan($withoutCoupon);
});
