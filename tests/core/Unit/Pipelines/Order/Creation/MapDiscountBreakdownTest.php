<?php

uses(\Lunar\Tests\Core\TestCase::class);

use Lunar\DiscountTypes\AmountOff;
use Lunar\Models\Cart;
use Lunar\Models\Channel;
use Lunar\Models\Currency;
use Lunar\Models\CustomerGroup;
use Lunar\Models\Discount;
use Lunar\Models\Order;
use Lunar\Models\Price;
use Lunar\Models\ProductVariant;
use Lunar\Pipelines\Order\Creation\CreateOrderLines;
use Lunar\Pipelines\Order\Creation\MapDiscountBreakdown;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    Currency::factory()->create([
        'code' => 'GBP',
        'decimal_places' => 2,
    ]);

    Channel::factory()->create([
        'default' => true,
    ]);

    CustomerGroup::factory()->create([
        'default' => true,
    ]);
});

test('can map discount with same purchasable with different meta', function () {
    $customerGroup = CustomerGroup::getDefault();

    $channel = Channel::getDefault();

    $currency = Currency::getDefault();

    $purchasable = ProductVariant::factory()->create();

    Price::factory()->create([
        'price' => 100,
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => $purchasable->getMorphClass(),
        'priceable_id' => $purchasable->id,
    ]);

    $discount = Discount::factory()->create([
        'type' => AmountOff::class,
        'name' => 'Test Coupon',
        'coupon' => '10OFF',
        'data' => [
            'fixed_value' => false,
            'percentage' => 10,
        ],
    ]);

    $discount->customerGroups()->sync([
        $customerGroup->id => [
            'enabled' => true,
            'starts_at' => now(),
        ],
    ]);

    $discount->channels()->sync([
        $channel->id => [
            'enabled' => true,
            'starts_at' => now()->subHour(),
        ],
    ]);

    $cart = Cart::factory()->create([
        'channel_id' => $channel->id,
        'currency_id' => $currency->id,
        'coupon_code' => '10OFF',
    ]);

    $cart->lines()->create([
        'purchasable_type' => $purchasable->getMorphClass(),
        'purchasable_id' => $purchasable->id,
        'quantity' => 1,
        'meta' => [
            'personalization' => 'Love you mum xxx',
        ]
    ]);

    $cart->lines()->create([
        'purchasable_type' => $purchasable->getMorphClass(),
        'purchasable_id' => $purchasable->id,
        'quantity' => 1,
        'meta' => [
            'personalization' => 'Get well soon',
        ]
    ]);

    $order = Order::factory()->create([
        'cart_id' => $cart->id,
    ]);

    $cart->calculate();

    app(CreateOrderLines::class)->handle($order, function ($order) {
        return $order;
    });

    app(MapDiscountBreakdown::class)->handle($order, function ($order) {
        return $order;
    });

    $appliedDiscount = $order->discount_breakdown->first();
    
    $appliedDiscountLines = $appliedDiscount->lines->map(function($line) {
        return $line->line->only('purchasable_type', 'purchasable_id', 'meta');
    });

    $orderLines = $order->lines->map(function ($line) {
        return $line->only('purchasable_type', 'purchasable_id', 'meta');
    });

    expect($appliedDiscountLines)->toHaveCount($orderLines->count());
    expect($appliedDiscountLines->toArray())->toEqual($orderLines->toArray());
});
