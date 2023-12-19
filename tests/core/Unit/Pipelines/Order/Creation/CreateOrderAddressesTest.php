<?php

uses(\Lunar\Tests\Core\TestCase::class);
use Lunar\Models\Cart;
use Lunar\Models\CartAddress;
use Lunar\Models\Currency;
use Lunar\Models\Order;
use Lunar\Models\OrderAddress;
use Lunar\Pipelines\Order\Creation\CreateOrderAddresses;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('can run pipeline', function () {
    $currency = Currency::factory()->create();

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
    ]);

    CartAddress::factory()->create([
        'type' => 'billing',
        'cart_id' => $cart->id,
    ]);

    CartAddress::factory()->create([
        'type' => 'shipping',
        'cart_id' => $cart->id,
    ]);

    $order = Order::factory()->create([
        'cart_id' => $cart->id,
    ]);

    app(CreateOrderAddresses::class)->handle($order, function ($order) {
        return $order;
    });

    expect($order->addresses)->toHaveCount($cart->addresses->count());
});

test('can update existing addresses', function () {
    $currency = Currency::factory()->create();

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
    ]);

    CartAddress::factory()->create([
        'type' => 'billing',
        'cart_id' => $cart->id,
        'postcode' => 'N1 1TW',
    ]);

    CartAddress::factory()->create([
        'type' => 'shipping',
        'cart_id' => $cart->id,
        'postcode' => 'N2 2TW',
    ]);

    $order = Order::factory()->create([
        'cart_id' => $cart->id,
    ]);

    OrderAddress::factory()->create([
        'type' => 'billing',
        'order_id' => $order->id,
        'postcode' => 'N1 1TW',
    ]);

    $address = OrderAddress::factory()->create([
        'type' => 'shipping',
        'order_id' => $order->id,
        'postcode' => 'N2 2TW',
    ]);

    app(CreateOrderAddresses::class)->handle($order, function ($order) {
        return $order;
    });

    expect($order->addresses)->toHaveCount($cart->addresses->count());
});
