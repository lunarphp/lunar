<?php

uses(\Lunar\Tests\Shipping\TestCase::class);

use Lunar\DataTypes\ShippingOption;
use Lunar\Models\Currency;
use Lunar\Models\TaxClass;
use Lunar\Shipping\DataTransferObjects\ShippingOptionRequest;
use Lunar\Shipping\Drivers\ShippingMethods\ShipBy;
use Lunar\Shipping\Models\ShippingMethod;
use Lunar\Shipping\Models\ShippingZone;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);
uses(\Lunar\Tests\Shipping\TestUtils::class);

test('can get shipping option by cart total', function () {
    $currency = Currency::factory()->create([
        'default' => true,
    ]);

    TaxClass::factory()->create([
        'default' => true,
    ]);

    $shippingZone = ShippingZone::factory()->create([
        'type' => 'countries',
    ]);

    $shippingMethod = ShippingMethod::factory()->create([
        'driver' => 'ship-by',
        'data' => [
            'charge_by' => 'cart_total',
        ],
    ]);

    $shippingRate = \Lunar\Shipping\Models\ShippingRate::factory()
        ->create([
            'shipping_method_id' => $shippingMethod->id,
            'shipping_zone_id' => $shippingZone->id,
        ]);

    $shippingRate->prices()->createMany([
        [
            'price' => 1000,
            'tier' => 1,
            'currency_id' => $currency->id,
        ],
        [
            'price' => 500,
            'tier' => 700,
            'currency_id' => $currency->id,
        ],
    ]);

    expect($shippingRate->prices)->toHaveCount(2);

    $cart = $this->createCart($currency, 100);

    $driver = new ShipBy();

    $request = new ShippingOptionRequest(
        shippingRate: $shippingRate,
        cart: $cart,
    );

    $shippingOption = $driver->resolve($request);

    expect($shippingOption)->toBeInstanceOf(ShippingOption::class);

    expect($shippingOption->price->value)->toEqual(1000);

    $cart = $this->createCart($currency, 10000);

    $driver = new ShipBy();

    $request = new ShippingOptionRequest(
        shippingRate: $shippingRate,
        cart: $cart
    );

    $shippingOption = $driver->resolve($request);

    expect($shippingOption)->toBeInstanceOf(ShippingOption::class);

    expect($shippingOption->price->value)->toEqual(500);
});

test('can get shipping option by cart total when prices include tax', function () {

    \Illuminate\Support\Facades\Config::set('lunar.pricing.stored_inclusive_of_tax', true);

    $currency = Currency::factory()->create([
        'default' => true,
    ]);

    TaxClass::factory()->create([
        'default' => true,
    ]);

    $shippingZone = ShippingZone::factory()->create([
        'type' => 'countries',
    ]);

    $shippingMethod = ShippingMethod::factory()->create([
        'driver' => 'ship-by',
        'data' => [
            'charge_by' => 'cart_total',
        ],
    ]);

    $shippingRate = \Lunar\Shipping\Models\ShippingRate::factory()
        ->create([
            'shipping_method_id' => $shippingMethod->id,
            'shipping_zone_id' => $shippingZone->id,
        ]);

    $shippingRate->prices()->createMany([
        [
            'price' => 1000,
            'tier' => 1,
            'currency_id' => $currency->id,
        ],
        [
            'price' => 500,
            'tier' => 700,
            'currency_id' => $currency->id,
        ],
    ]);

    expect($shippingRate->prices)->toHaveCount(2);

    $cart = $this->createCart($currency, 700);

    $driver = new ShipBy();

    $request = new ShippingOptionRequest(
        shippingRate: $shippingRate,
        cart: $cart,
    );

    $shippingOption = $driver->resolve($request);

    expect($shippingOption)->toBeInstanceOf(ShippingOption::class);

    expect($shippingOption->price->value)->toEqual(500);

})->group('thisone');

test('can get shipping option if outside tier without default price', function () {
    // Boom.
    $currency = Currency::factory()->create([
        'default' => true,
    ]);

    TaxClass::factory()->create([
        'default' => true,
    ]);

    $shippingZone = ShippingZone::factory()->create([
        'type' => 'countries',
    ]);

    $shippingMethod = ShippingMethod::factory()->create([
        'driver' => 'ship-by',
        'data' => [
            'charge_by' => 'cart_total',
        ],
    ]);

    $shippingRate = \Lunar\Shipping\Models\ShippingRate::factory()
        ->create([
            'shipping_method_id' => $shippingMethod->id,
            'shipping_zone_id' => $shippingZone->id,
        ]);

    $shippingRate->prices()->createMany([
        [
            'price' => 500,
            'tier' => 700,
            'currency_id' => $currency->id,
        ],
    ]);

    expect($shippingRate->prices)->toHaveCount(1);

    $cart = $this->createCart($currency, 100);

    $driver = new ShipBy();

    $request = new ShippingOptionRequest(
        shippingRate: $shippingRate,
        cart: $cart
    );

    $this->expectException(\ErrorException::class);

    $driver->resolve($request);
});
