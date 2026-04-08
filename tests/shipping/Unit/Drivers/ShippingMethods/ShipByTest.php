<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Lunar\DataTypes\ShippingOption;
use Lunar\Models\Currency;
use Lunar\Models\TaxClass;
use Lunar\Shipping\DataTransferObjects\ShippingOptionRequest;
use Lunar\Shipping\Drivers\ShippingMethods\ShipBy;
use Lunar\Shipping\Models\ShippingMethod;
use Lunar\Shipping\Models\ShippingRate;
use Lunar\Shipping\Models\ShippingZone;
use Lunar\Tests\Shipping\TestCase;
use Lunar\Tests\Shipping\TestUtils;

uses(TestCase::class);

uses(RefreshDatabase::class);
uses(TestUtils::class);

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

    $shippingRate = ShippingRate::factory()
        ->create([
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
            'price' => 500,
            'min_quantity' => 700,
            'currency_id' => $currency->id,
        ],
    ]);

    expect($shippingRate->prices)->toHaveCount(2);

    $cart = $this->createCart($currency, 100);

    $driver = new ShipBy;

    $request = new ShippingOptionRequest(
        shippingRate: $shippingRate,
        cart: $cart,
    );

    $shippingOption = $driver->resolve($request);

    expect($shippingOption)->toBeInstanceOf(ShippingOption::class);

    expect($shippingOption->price->value)->toEqual(1000);

    $cart = $this->createCart($currency, 10000);

    $driver = new ShipBy;

    $request = new ShippingOptionRequest(
        shippingRate: $shippingRate,
        cart: $cart
    );

    $shippingOption = $driver->resolve($request);

    expect($shippingOption)->toBeInstanceOf(ShippingOption::class);

    expect($shippingOption->price->value)->toEqual(500);
});

test('can get shipping option by cart total when prices include tax', function () {

    Config::set('lunar.pricing.stored_inclusive_of_tax', true);

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

    $shippingRate = ShippingRate::factory()
        ->create([
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
            'price' => 500,
            'min_quantity' => 700,
            'currency_id' => $currency->id,
        ],
    ]);

    expect($shippingRate->prices)->toHaveCount(2);

    $cart = $this->createCart($currency, 700);

    $driver = new ShipBy;

    $request = new ShippingOptionRequest(
        shippingRate: $shippingRate,
        cart: $cart,
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
        'driver' => 'ship-by',
        'data' => [
            'charge_by' => 'cart_total',
        ],
    ]);

    $shippingRate = ShippingRate::factory()
        ->create([
            'shipping_method_id' => $shippingMethod->id,
            'shipping_zone_id' => $shippingZone->id,
        ]);

    $shippingRate->prices()->createMany([
        [
            'price' => 500,
            'min_quantity' => 700,
            'currency_id' => $currency->id,
        ],
    ]);

    expect($shippingRate->prices)->toHaveCount(1);

    $cart = $this->createCart($currency, 100);

    $driver = new ShipBy;

    $request = new ShippingOptionRequest(
        shippingRate: $shippingRate,
        cart: $cart
    );

    $this->expectException(ErrorException::class);

    $driver->resolve($request);
});

test('returns null when cart currency has no shipping price', function () {
    $defaultCurrency = Currency::factory()->create([
        'default' => true,
        'decimal_places' => 2,
        'code' => 'USD',
    ]);

    $nonDefaultCurrency = Currency::factory()->create([
        'default' => false,
        'decimal_places' => 2,
        'code' => 'EUR',
        'enabled' => true,
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

    // Only a USD base price — no EUR price
    $shippingRate->prices()->create([
        'price' => 1000,
        'min_quantity' => 1,
        'currency_id' => $defaultCurrency->id,
    ]);

    // Cart is in EUR
    $cart = $this->createCart($nonDefaultCurrency, 500);

    $driver = new ShipBy;

    $request = new ShippingOptionRequest(
        shippingRate: $shippingRate,
        cart: $cart
    );

    $shippingOption = $driver->resolve($request);

    expect($shippingOption)->toBeNull();
});

test('uses correct currency price for cart currency', function () {
    $usd = Currency::factory()->create([
        'default' => true,
        'decimal_places' => 2,
        'code' => 'USD',
    ]);

    $eur = Currency::factory()->create([
        'default' => false,
        'decimal_places' => 2,
        'code' => 'EUR',
        'enabled' => true,
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
            'min_quantity' => 1,
            'currency_id' => $usd->id,
        ],
        [
            'price' => 900,
            'min_quantity' => 1,
            'currency_id' => $eur->id,
        ],
    ]);

    // EUR cart should use EUR price
    $eurCart = $this->createCart($eur, 500);

    $driver = new ShipBy;

    $request = new ShippingOptionRequest(
        shippingRate: $shippingRate,
        cart: $eurCart
    );

    $shippingOption = $driver->resolve($request);

    expect($shippingOption)->toBeInstanceOf(ShippingOption::class);
    expect($shippingOption->price->value)->toEqual(900);

    // USD cart should use USD price
    $usdCart = $this->createCart($usd, 500);

    $request = new ShippingOptionRequest(
        shippingRate: $shippingRate,
        cart: $usdCart
    );

    $shippingOption = (new ShipBy)->resolve($request);

    expect($shippingOption)->toBeInstanceOf(ShippingOption::class);
    expect($shippingOption->price->value)->toEqual(1000);
});

test('uses correct currency price break for cart currency', function () {
    $usd = Currency::factory()->create([
        'default' => true,
        'decimal_places' => 2,
        'code' => 'USD',
    ]);

    $eur = Currency::factory()->create([
        'default' => false,
        'decimal_places' => 2,
        'code' => 'EUR',
        'enabled' => true,
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
        // USD: $10 base, free over $100
        ['price' => 1000, 'min_quantity' => 1, 'currency_id' => $usd->id],
        ['price' => 0, 'min_quantity' => 10000, 'currency_id' => $usd->id],
        // EUR: €8 base, free over €80
        ['price' => 800, 'min_quantity' => 1, 'currency_id' => $eur->id],
        ['price' => 0, 'min_quantity' => 8000, 'currency_id' => $eur->id],
    ]);

    // EUR cart over €80 threshold — should be free
    $eurCart = $this->createCart($eur, 8000);

    $request = new ShippingOptionRequest(
        shippingRate: $shippingRate,
        cart: $eurCart
    );

    $shippingOption = (new ShipBy)->resolve($request);

    expect($shippingOption)->toBeInstanceOf(ShippingOption::class);
    expect($shippingOption->price->value)->toEqual(0);

    // EUR cart under €80 threshold — should be €8
    $eurCartSmall = $this->createCart($eur, 500);

    $request = new ShippingOptionRequest(
        shippingRate: $shippingRate,
        cart: $eurCartSmall
    );

    $shippingOption = (new ShipBy)->resolve($request);

    expect($shippingOption)->toBeInstanceOf(ShippingOption::class);
    expect($shippingOption->price->value)->toEqual(800);
});
