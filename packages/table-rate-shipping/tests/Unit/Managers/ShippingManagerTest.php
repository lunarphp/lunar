<?php

uses(\Lunar\Shipping\Tests\TestCase::class);
use Lunar\Models\CartAddress;
use Lunar\Models\Country;
use Lunar\Models\Currency;
use Lunar\Models\TaxClass;
use Lunar\Shipping\Facades\Shipping;
use Lunar\Shipping\Models\ShippingMethod;
use Lunar\Shipping\Models\ShippingZone;
use Lunar\Shipping\Resolvers\ShippingZoneResolver;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);
uses(\Lunar\Shipping\Tests\TestUtils::class);

test('zones method uses shipping zone resolver', function () {
    $resolver = Shipping::zones();
    expect($resolver)->toBeInstanceOf(ShippingZoneResolver::class);
});

test('can fetch expected shipping methods', function () {
    $currency = Currency::factory()->create([
        'default' => true,
    ]);

    $country = Country::factory()->create();

    TaxClass::factory()->create([
        'default' => true,
    ]);

    $shippingZone = ShippingZone::factory()->create([
        'type' => 'countries',
    ]);

    $shippingZone->countries()->attach($country);

    $shippingMethod = ShippingMethod::factory()->create([
        'shipping_zone_id' => $shippingZone->id,
        'driver' => 'ship-by',
        'data' => [
            'minimum_spend' => [
                "{$currency->code}" => 200,
            ],
        ],
    ]);

    $shippingMethod->prices()->createMany([
        [
            'price' => 600,
            'tier' => 1,
            'currency_id' => $currency->id,
        ],
        [
            'price' => 500,
            'tier' => 700,
            'currency_id' => $currency->id,
        ],
        [
            'price' => 0,
            'tier' => 800,
            'currency_id' => $currency->id,
        ],
    ]);

    $cart = $this->createCart($currency, 500);

    $cart->shippingAddress()->create(
        CartAddress::factory()->make([
            'country_id' => $country->id,
            'state' => null,
        ])->toArray()
    );

    $shippingMethods = Shipping::shippingMethods(
        $cart->refresh()->calculate()
    )->get();

    expect($shippingMethods)->toHaveCount(1);
});
