<?php

uses(\Lunar\Tests\Shipping\TestCase::class);

use Lunar\DataTypes\ShippingOption;
use Lunar\Models\Currency;
use Lunar\Models\TaxClass;
use Lunar\Shipping\DataTransferObjects\ShippingOptionRequest;
use Lunar\Shipping\Drivers\ShippingMethods\Collection;
use Lunar\Shipping\Models\ShippingMethod;
use Lunar\Shipping\Models\ShippingZone;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);
uses(\Lunar\Tests\Shipping\TestUtils::class);

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
        'data' => [],
    ]);

    $cart = $this->createCart($currency, 500);

    $driver = new Collection();

    $request = new ShippingOptionRequest(
        cart: $cart,
        shippingMethod: $shippingMethod
    );

    $shippingOption = $driver->resolve($request);

    expect($shippingOption)->toBeInstanceOf(ShippingOption::class);

    expect($shippingOption->price->value)->toEqual(0);
});
