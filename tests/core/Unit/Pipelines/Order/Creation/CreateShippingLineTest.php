<?php

uses(\Lunar\Tests\Core\TestCase::class);
use Lunar\DataTypes\Price;
use Lunar\DataTypes\ShippingOption;
use Lunar\Facades\ShippingManifest;
use Lunar\Models\Cart;
use Lunar\Models\CartAddress;
use Lunar\Models\Currency;
use Lunar\Models\Order;
use Lunar\Models\OrderLine;
use Lunar\Models\TaxClass;
use Lunar\Pipelines\Order\Creation\CreateShippingLine;

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

    ShippingManifest::addOption(
        new ShippingOption(
            name: 'Basic Delivery',
            description: 'Basic Delivery',
            identifier: 'BASDEL',
            price: new Price(500, $cart->currency, 1),
            taxClass: TaxClass::factory()->create()
        )
    );

    CartAddress::factory()->create([
        'type' => 'shipping',
        'shipping_option' => 'BASDEL',
        'cart_id' => $cart->id,
    ]);

    $order = Order::factory()->create([
        'cart_id' => $cart->id,
    ]);

    $order = app(CreateShippingLine::class)->handle($order, function ($order) {
        return $order;
    });

    expect($order->shippingLines)->toHaveCount(1);
    expect($order->shippingLines->first()->identifier)->toEqual('BASDEL');
});

test('can update shipping line if exists', function () {
    $currency = Currency::factory()->create();

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
    ]);

    CartAddress::factory()->create([
        'type' => 'billing',
        'cart_id' => $cart->id,
    ]);

    ShippingManifest::addOption(
        new ShippingOption(
            name: 'Basic Delivery',
            description: 'Basic Delivery',
            identifier: 'BASDEL',
            price: new Price(500, $cart->currency, 1),
            taxClass: TaxClass::factory()->create()
        )
    );

    CartAddress::factory()->create([
        'type' => 'shipping',
        'shipping_option' => 'BASDEL',
        'cart_id' => $cart->id,
    ]);

    $order = Order::factory()->create([
        'cart_id' => $cart->id,
    ]);

    OrderLine::factory()->create([
        'identifier' => 'BASDEL',
        'purchasable_type' => ShippingOption::class,
        'type' => 'shipping',
        'order_id' => $order->id,
    ]);

    $order = app(CreateShippingLine::class)->handle($order->refresh(), function ($order) {
        return $order;
    });

    expect($order->shippingLines)->toHaveCount(1);
    expect($order->shippingLines->first()->identifier)->toEqual('BASDEL');
});
