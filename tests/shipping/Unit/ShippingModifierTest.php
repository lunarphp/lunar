<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Models\CartAddress;
use Lunar\Models\Country;
use Lunar\Models\Currency;
use Lunar\Models\CustomerGroup;
use Lunar\Models\TaxClass;
use Lunar\Shipping\Models\ShippingMethod;
use Lunar\Shipping\Models\ShippingRate;
use Lunar\Shipping\Models\ShippingZone;
use Lunar\Tests\Shipping\TestCase;
use Lunar\Tests\Shipping\TestUtils;

uses(TestCase::class)->group('shipping', 'shipping-modifier');
uses(RefreshDatabase::class);
uses(TestUtils::class);

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

    $customerGroup = CustomerGroup::factory()->create([
        'default' => true,
    ]);
    $shippingMethod->customerGroups()->sync([
        $customerGroup->id => ['enabled' => true, 'visible' => true, 'starts_at' => now(), 'ends_at' => null],
    ]);

    $shippingRate = ShippingRate::factory()->create([
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
        CartAddress::factory()->make([
            'country_id' => $country->id,
            'shipping_option' => 'BASEDEL',
            'state' => null,
            'type' => 'shipping',
        ])->toArray()
    );

    $option = $cart->refresh()->getShippingOption();

    expect($option->price->value)->toBe(0);
});
