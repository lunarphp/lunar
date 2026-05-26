<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\DataObjects\PriceValue;
use Lunar\Core\DataTypes\ShippingOption;
use Lunar\Core\Facades\ShippingManifest;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\CartAddress;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Order;
use Lunar\Core\Models\OrderLine;
use Lunar\Core\Models\TaxClass;
use Lunar\Core\Pipelines\Order\Creation\CreateShippingLine;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);

uses(RefreshDatabase::class);

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
            price: new PriceValue(500, $cart->currency),
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
            price: new PriceValue(500, $cart->currency),
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
