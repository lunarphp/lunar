<?php

uses(\Lunar\Tests\TestCase::class);
use Lunar\Base\Casts\ShippingBreakdown as ShippingBreakdownCasts;
use Lunar\Base\ValueObjects\Cart\ShippingBreakdown;
use Lunar\Base\ValueObjects\Cart\ShippingBreakdownItem;
use Lunar\DataTypes\Price;
use Lunar\Models\Currency;
use Lunar\Models\Order;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('can set from value object', function () {
    $currency = Currency::factory()->create();
    $order = Order::factory()->create();

    $shippingBreakdownValueObject = new ShippingBreakdown();

    $shippingBreakdownValueObject->items->put('DELIV',
        new ShippingBreakdownItem(
            name: 'Basic Delivery',
            identifier: 'DELIV',
            price: new Price(700, $currency, 1),
        )
    );

    $breakDown = new ShippingBreakdownCasts;

    $result = $breakDown->set($order, 'shipping_breakdown', $shippingBreakdownValueObject, []);

    expect($result)->toHaveKey('shipping_breakdown');
    expect($result['shipping_breakdown'])->toBeJson();
});

test('can cast to and from model', function () {
    $currency = Currency::factory()->create();
    $order = Order::factory()->create();

    $shippingBreakdownValueObject = new ShippingBreakdown();

    $shippingBreakdownValueObject->items->put('DELIV',
        new ShippingBreakdownItem(
            name: 'Basic Delivery',
            identifier: 'DELIV',
            price: new Price(700, $currency, 1),
        )
    );

    $order->update([
        'shipping_breakdown' => $shippingBreakdownValueObject,
    ]);

    $breakdown = $order->refresh()->shipping_breakdown;
    expect($breakdown)->toBeInstanceOf(ShippingBreakdown::class);
});
