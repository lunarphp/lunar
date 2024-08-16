<?php

use Lunar\Models\Country;
use Lunar\Models\Currency;
use Lunar\Models\TaxClass;
use Lunar\Shipping\Models\ShippingMethod;
use Lunar\Shipping\Models\ShippingZone;

uses(\Lunar\Tests\Shipping\TestCase::class);
uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);
uses(\Lunar\Tests\Shipping\TestUtils::class);

test('can set correct shipping options', function () {
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
        'driver' => 'ship-by',
        'code' => 'BASEDEL',
        'data' => [
            'minimum_spend' => [
                "{$currency->code}" => 200,
            ],
        ],
    ]);

    $shippingRate = \Lunar\Shipping\Models\ShippingRate::factory()->create([
        'shipping_method_id' => $shippingMethod->id,
        'shipping_zone_id' => $shippingZone->id,
    ]);

    $shippingRate->prices()->createMany([
        [
            'price' => 1000,
            'min_quantity' => 1,
            'currency_id' => $currency->id,
        ],
        [
            'price' => 0,
            'min_quantity' => 500,
            'currency_id' => $currency->id,
        ],
    ]);

    $cart = $this->createCart($currency, 6000, calculate: false);

    $cart->shippingAddress()->create(
        \Lunar\Models\CartAddress::factory()->make([
            'country_id' => $country->id,
            'shipping_option' => 'BASEDEL',
            'state' => null,
            'type' => 'shipping',
        ])->toArray()
    );

    $option = $cart->refresh()->getShippingOption();

    expect($option->price->value)->toBe(0);
})->group('shipping-modifier');
