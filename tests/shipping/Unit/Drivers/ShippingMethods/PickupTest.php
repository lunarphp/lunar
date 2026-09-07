<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\DataTypes\ShippingOption;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\TaxClass;
use Lunar\Shipping\DataTransferObjects\ShippingOptionRequest;
use Lunar\Shipping\Drivers\ShippingMethods\Pickup;
use Lunar\Shipping\Models\ShippingMethod;
use Lunar\Shipping\Models\ShippingRate;
use Lunar\Shipping\Models\ShippingZone;
use Lunar\Tests\Shipping\TestCase;
use Lunar\Tests\Shipping\TestUtils;

uses(TestCase::class)->group('shipping', 'shipping-driver', 'shipping-driver-pickup');

uses(RefreshDatabase::class);
uses(TestUtils::class);

test('can get a zero-priced pickup option', function () {
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
        'driver' => 'pickup',
        'data' => [],
    ]);

    $shippingRate = ShippingRate::factory()->create([
        'shipping_method_id' => $shippingMethod->id,
        'shipping_zone_id' => $shippingZone->id,
    ]);

    $cart = $this->createCart($currency, 500);

    $driver = new Pickup;

    $request = new ShippingOptionRequest(
        cart: $cart,
        shippingRate: $shippingRate
    );

    $shippingOption = $driver->resolve($request);

    expect($shippingOption)->toBeInstanceOf(ShippingOption::class);

    expect($shippingOption->price->value)->toEqual(0);

    expect($shippingOption->pickup)->toBeTrue();
});
