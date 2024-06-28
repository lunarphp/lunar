<?php

uses(\Lunar\Tests\Shipping\TestCase::class);

use Lunar\Models\CartAddress;
use Lunar\Models\Country;
use Lunar\Models\Currency;
use Lunar\Models\TaxClass;
use Lunar\Shipping\DataTransferObjects\ShippingOptionLookup;
use Lunar\Shipping\Facades\Shipping;
use Lunar\Shipping\Models\ShippingMethod;
use Lunar\Shipping\Models\ShippingZone;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);
uses(\Lunar\Tests\Shipping\TestUtils::class);

test('can fetch shipping options', function () {
    $currency = Currency::factory()->create([
        'default' => true,
    ]);

    $country = Country::factory()->create();

    TaxClass::factory()->create([
        'default' => true,
    ]);
    $customerGroup = \Lunar\Models\CustomerGroup::factory()->create([
        'default' => true,
    ]);

    $shippingZone = ShippingZone::factory()->create([
        'type' => 'countries',
    ]);

    $shippingZone->countries()->attach($country);

    $shippingMethod = ShippingMethod::factory()->create([
        'driver' => 'ship-by',
        'data' => [
            'minimum_spend' => [
                "{$currency->code}" => 200,
            ],
        ],
    ]);

    $shippingMethod->customerGroups()->sync([
        $customerGroup->id => ['enabled' => true, 'visible' => true, 'starts_at' => now(), 'ends_at' => null],
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
        CartAddress::factory()->make([
            'country_id' => $country->id,
            'state' => null,
        ])->toArray()
    );

    $shippingRates = Shipping::shippingRates(
        $cart->refresh()->calculate()
    )->get();

    $options = Shipping::shippingOptions()->cart(
        $cart->refresh()->calculate()
    )->get(
        new ShippingOptionLookup(
            shippingRates: $shippingRates
        )
    );

    $this->assertcount(1, $options);
});
