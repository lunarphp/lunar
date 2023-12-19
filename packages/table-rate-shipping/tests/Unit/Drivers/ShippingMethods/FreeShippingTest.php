<?php

uses(\Lunar\Shipping\Tests\TestCase::class);
use Lunar\DataTypes\ShippingOption;
use Lunar\Models\Currency;
use Lunar\Models\TaxClass;
use Lunar\Shipping\DataTransferObjects\ShippingOptionRequest;
use Lunar\Shipping\Drivers\ShippingMethods\FreeShipping;
use Lunar\Shipping\Facades\Shipping;
use Lunar\Shipping\Models\ShippingMethod;
use Lunar\Shipping\Models\ShippingZone;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);
uses(\Lunar\Shipping\Tests\TestUtils::class);

test('can get free shipping', function () {
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
        'driver' => 'free-shipping',
        'data' => [
            'minimum_spend' => [
                "{$currency->code}" => 500,
            ],
        ],
    ]);

    $cart = $this->createCart($currency, 500);

    $driver = new FreeShipping();

    $request = new ShippingOptionRequest(
        cart: $cart,
        shippingMethod: $shippingMethod
    );

    $shippingOption = $driver->resolve($request);

    expect($shippingOption)->toBeInstanceOf(ShippingOption::class);
});

test('cant get free shipping if minimum isnt met', function () {
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
        'driver' => 'free-shipping',
        'data' => [
            'minimum_spend' => [
                "{$currency->code}" => 500,
            ],
        ],
    ]);

    $cart = $this->createCart($currency, 50);

    $driver = new FreeShipping();

    $request = new ShippingOptionRequest(
        cart: $cart,
        shippingMethod: $shippingMethod
    );

    $shippingOption = $driver->resolve($request);

    expect($shippingOption)->toBeNull();
});

test('cant get free shipping if currency isnt met', function () {
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
        'driver' => 'free-shipping',
        'data' => [
            'minimum_spend' => [
                'FOO' => 500,
            ],
        ],
    ]);

    $cart = $this->createCart($currency, 10000);

    $driver = new FreeShipping();

    $request = new ShippingOptionRequest(
        cart: $cart,
        shippingMethod: $shippingMethod
    );

    $shippingOption = $driver->resolve($request);

    expect($shippingOption)->toBeNull();
});
