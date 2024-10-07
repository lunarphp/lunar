<?php

uses(\Lunar\Tests\Core\TestCase::class);

use Lunar\Models\Cart;
use Lunar\Models\Currency;
use Lunar\Models\Order;
use Lunar\Models\Price;
use Lunar\Models\ProductVariant;
use Lunar\Pipelines\Order\Creation\FillOrderFromCart;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('can run pipeline', function () {
    $currency = Currency::factory()->create();

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
    ]);

    $purchasable = ProductVariant::factory()->create();

    Price::factory()->create([
        'price' => 100,
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => $purchasable->getMorphClass(),
        'priceable_id' => $purchasable->id,
    ]);

    $cart->lines()->create([
        'purchasable_type' => $purchasable->getMorphClass(),
        'purchasable_id' => $purchasable->id,
        'quantity' => 1,
    ]);

    /** @var Order $order */
    $order = new Order([
        'cart_id' => $cart->id,
    ]);

    $cart->calculate();

    app(FillOrderFromCart::class)->handle($order, function ($order) {
        return $order;
    });

    expect($order->reference)->not->toBeNull();
    expect($order->user_id)->toEqual($cart->user_id);
    expect($order->channel_id)->toEqual($cart->channel_id);
    expect($order->sub_total->value)->toEqual($cart->subTotal->value);
    expect($order->discount_otal?->value)->toEqual($cart->discountTotal?->value);
    expect($order->tax_total->value)->toEqual($cart->taxTotal->value);
    expect($order->total->value)->toEqual($cart->total->value);
});
