<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Base\Casts\ShippingBreakdown as ShippingBreakdownCasts;
use Lunar\Core\Base\ValueObjects\Cart\ShippingBreakdown;
use Lunar\Core\Base\ValueObjects\Cart\ShippingBreakdownItem;
use Lunar\Core\DataObjects\PriceValue;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Order;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);

uses(RefreshDatabase::class);

test('can set from value object', function () {
    $currency = Currency::factory()->create();
    $order = Order::factory()->create();

    $shippingBreakdownValueObject = new ShippingBreakdown;

    $shippingBreakdownValueObject->items->put('DELIV',
        new ShippingBreakdownItem(
            name: 'Basic Delivery',
            identifier: 'DELIV',
            price: new PriceValue(700, $currency),
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

    $shippingBreakdownValueObject = new ShippingBreakdown;

    $shippingBreakdownValueObject->items->put('DELIV',
        new ShippingBreakdownItem(
            name: 'Basic Delivery',
            identifier: 'DELIV',
            price: new PriceValue(700, $currency),
        )
    );

    $order->update([
        'shipping_breakdown' => $shippingBreakdownValueObject,
    ]);

    $breakdown = $order->refresh()->shipping_breakdown;
    expect($breakdown)->toBeInstanceOf(ShippingBreakdown::class);
});
