<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\DataTypes\ShippingOption;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Location;
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

    $defaultLocation = Location::factory()->create(['default' => true]);

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

    expect($shippingOption->isPickup())->toBeTrue()
        ->and($shippingOption->pickupLocation->id)->toBe($defaultLocation->id);
});

test('a pickup option collects from its shipping method\'s location', function () {
    Currency::factory()->create(['default' => true]);
    TaxClass::factory()->create(['default' => true]);
    Location::factory()->create(['default' => true]);
    $store = Location::factory()->create();

    $shippingRate = ShippingRate::factory()->create([
        'shipping_method_id' => ShippingMethod::factory()->create([
            'driver' => 'pickup',
            'data' => [],
            'pickup_location_id' => $store->id,
        ])->id,
        'shipping_zone_id' => ShippingZone::factory()->create(['type' => 'countries'])->id,
    ]);

    $shippingOption = (new Pickup)->resolve(new ShippingOptionRequest(
        cart: $this->createCart(Currency::getDefault(), 500),
        shippingRate: $shippingRate,
    ));

    expect($shippingOption->pickupLocation->id)->toBe($store->id);
});
