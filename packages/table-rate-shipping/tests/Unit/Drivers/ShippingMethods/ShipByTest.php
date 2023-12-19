<?php

uses(\Lunar\Shipping\Tests\TestCase::class);
use Lunar\DataTypes\ShippingOption;
use Lunar\Models\Currency;
use Lunar\Models\TaxClass;
use Lunar\Shipping\DataTransferObjects\ShippingOptionRequest;
use Lunar\Shipping\Drivers\ShippingMethods\ShipBy;
use Lunar\Shipping\Facades\Shipping;
use Lunar\Shipping\Models\ShippingMethod;
use Lunar\Shipping\Models\ShippingZone;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);
uses(\Lunar\Shipping\Tests\TestUtils::class);

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
        'shipping_zone_id' => $shippingZone->id,
        'driver' => 'ship-by',
        'data' => [
            'charge_by' => 'cart_total',
        ],
    ]);

    $shippingMethod->prices()->createMany([
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

    expect($shippingMethod->prices)->toHaveCount(2);

    $cart = $this->createCart($currency, 100);

    $driver = new ShipBy();

    $request = new ShippingOptionRequest(
        cart: $cart,
        shippingMethod: $shippingMethod
    );

    $shippingOption = $driver->resolve($request);

    expect($shippingOption)->toBeInstanceOf(ShippingOption::class);

    expect($shippingOption->price->value)->toEqual(1000);

    $cart = $this->createCart($currency, 10000);

    $driver = new ShipBy();

    $request = new ShippingOptionRequest(
        cart: $cart,
        shippingMethod: $shippingMethod
    );

    $shippingOption = $driver->resolve($request);

    expect($shippingOption)->toBeInstanceOf(ShippingOption::class);

    expect($shippingOption->price->value)->toEqual(500);
});

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
        'shipping_zone_id' => $shippingZone->id,
        'driver' => 'ship-by',
        'data' => [
            'charge_by' => 'cart_total',
        ],
    ]);

    $shippingMethod->prices()->createMany([
        [
            'price' => 500,
            'tier' => 700,
            'currency_id' => $currency->id,
        ],
    ]);

    expect($shippingMethod->prices)->toHaveCount(1);

    $cart = $this->createCart($currency, 100);

    $driver = new ShipBy();

    $request = new ShippingOptionRequest(
        cart: $cart,
        shippingMethod: $shippingMethod
    );

    $this->expectException(\ErrorException::class);

    $driver->resolve($request);
});
