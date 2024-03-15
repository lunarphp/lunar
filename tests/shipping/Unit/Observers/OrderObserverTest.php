<?php

use Lunar\Models\Order;
use Lunar\Shipping\Observers\OrderObserver;

uses(\Lunar\Tests\Shipping\TestCase::class);
uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);
uses(\Lunar\Tests\Shipping\TestUtils::class);

test('can store shipping zone against order', function () {

    Order::observe(OrderObserver::class);

    $currency = \Lunar\Models\Currency::factory()->create([
        'default' => true,
    ]);

    $country = \Lunar\Models\Country::factory()->create();

    \Lunar\Models\TaxClass::factory()->create([
        'default' => true,
    ]);

    $shippingZone = \Lunar\Shipping\Models\ShippingZone::factory()->create([
        'type' => 'countries',
    ]);

    $shippingZone->countries()->attach($country);

    $shippingMethod = \Lunar\Shipping\Models\ShippingMethod::factory()->create([
        'driver' => 'ship-by',
        'data' => [
            'minimum_spend' => [
                "{$currency->code}" => 200,
            ],
        ],
    ]);

    $shippingRate = \Lunar\Shipping\Models\ShippingRate::factory()
        ->create([
            'shipping_method_id' => $shippingMethod->id,
            'shipping_zone_id' => $shippingZone->id,
        ]);

    $shippingRate->prices()->createMany([
        [
            'price' => 600,
            'min_quantity' => 1,
            'currency_id' => $currency->id,
        ],
        [
            'price' => 500,
            'min_quantity' => 700,
            'currency_id' => $currency->id,
        ],
        [
            'price' => 0,
            'min_quantity' => 800,
            'currency_id' => $currency->id,
        ],
    ]);

    $cart = $this->createCart($currency, 500);

    $cart->shippingAddress()->create(
        \Lunar\Models\CartAddress::factory()->make([
            'country_id' => $country->id,
            'state' => null,
        ])->toArray()
    );

    $cart->billingAddress()->create(
        \Lunar\Models\CartAddress::factory()->make([
            'country_id' => $country->id,
            'type' => 'billing',
            'state' => null,
        ])->toArray()
    );

    $shippingOption = \Lunar\Facades\ShippingManifest::getOptions($cart->refresh())->first();

    $cart->setShippingOption($shippingOption);

    $order = $cart->refresh()->createOrder();
    $orderShippingZone = $order->shippingZone->first();

    expect($orderShippingZone)->toBeInstanceOf(\Lunar\Shipping\Models\ShippingZone::class)
        ->and($orderShippingZone->id)
        ->toBe($shippingZone->id);
});
