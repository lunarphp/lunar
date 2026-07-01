<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\DiscountTypes\AmountOff;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\Channel;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Core\Models\Discount;
use Lunar\Core\Models\Price;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\Models\Promotion;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

beforeEach(function () {
    Currency::factory()->create(['code' => 'GBP', 'decimal_places' => 2]);
    Channel::factory()->create(['default' => true]);
    CustomerGroup::factory()->create(['default' => true]);
});

function cartWithPercentageDiscount(?Promotion $promotion): array
{
    $channel = Channel::getDefault();
    $currency = Currency::getDefault();
    $customerGroup = CustomerGroup::getDefault();

    $cart = Cart::factory()->create([
        'channel_id' => $channel->id,
        'currency_id' => $currency->id,
        'coupon_code' => '10OFF',
    ]);

    $variant = ProductVariant::factory()->create([
        'product_id' => Product::factory()->create()->id,
    ]);

    Price::factory()->create([
        'price' => 1000,
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => $variant->getMorphClass(),
        'priceable_id' => $variant->id,
    ]);

    $cart->lines()->create([
        'purchasable_type' => $variant->getMorphClass(),
        'purchasable_id' => $variant->id,
        'quantity' => 1,
    ]);

    $discount = Discount::factory()->create([
        'type' => AmountOff::class,
        'name' => 'Ten percent',
        'coupon' => '10OFF',
        'promotion_id' => $promotion?->id,
        'data' => ['fixed_value' => false, 'percentage' => 10],
    ]);

    $discount->customerGroups()->sync([
        $customerGroup->id => ['enabled' => true, 'starts_at' => now()->subHour()],
    ]);
    $discount->channels()->sync([
        $channel->id => ['enabled' => true, 'starts_at' => now()->subHour()],
    ]);

    return [$cart, $discount];
}

test('a discount under an active promotion applies and surfaces on the cart', function () {
    $promotion = Promotion::factory()->create([
        'name' => ['en' => 'World Cup 2026'],
        'handle' => 'world-cup-2026',
        'starts_at' => now()->subDay(),
        'ends_at' => now()->addDay(),
    ]);

    [$cart] = cartWithPercentageDiscount($promotion);

    $cart = $cart->calculate();

    expect($cart->discountTotal->value)->toEqual(100);
    expect($cart->promotions)->toHaveCount(1);

    $surfaced = $cart->promotions->first();
    expect($surfaced->reference)->toBe('world-cup-2026');
    expect($surfaced->description)->toBe('World Cup 2026');
    expect($surfaced->amount->value)->toEqual(100);
});

test('a discount under an expired promotion does not apply', function () {
    $promotion = Promotion::factory()->create([
        'starts_at' => now()->subWeek(),
        'ends_at' => now()->subDay(),
    ]);

    [$cart] = cartWithPercentageDiscount($promotion);

    $cart = $cart->calculate();

    expect($cart->discountTotal->value)->toEqual(0);
    expect($cart->promotions)->toHaveCount(0);
});

test('a standalone discount still applies and surfaces no promotion', function () {
    [$cart] = cartWithPercentageDiscount(null);

    $cart = $cart->calculate();

    expect($cart->discountTotal->value)->toEqual(100);
    expect($cart->promotions)->toHaveCount(0);
});
