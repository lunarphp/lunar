<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\DataTypes\ShippingOption;
use Lunar\Models\Cart;
use Lunar\Models\Currency;
use Lunar\Models\Price;
use Lunar\Models\ProductVariant;
use Lunar\Models\TaxClass;
use Lunar\Shipping\DataTransferObjects\ShippingOptionRequest;
use Lunar\Shipping\Drivers\ShippingMethods\ShipBy;
use Lunar\Shipping\Models\ShippingMethod;
use Lunar\Shipping\Models\ShippingRate;
use Lunar\Shipping\Models\ShippingZone;
use Lunar\Tests\Shipping\TestCase;
use Lunar\Tests\Shipping\TestUtils;

uses(TestCase::class)->group('shipping', 'shipping-driver', 'shipping-driver-shipby');

uses(RefreshDatabase::class);
uses(TestUtils::class);

test('weight-based shipping uses configurable weight_unit from shipping method data', function () {
    $currency = Currency::factory()->create(['default' => true]);
    TaxClass::factory()->create(['default' => true]);

    $shippingZone = ShippingZone::factory()->create(['type' => 'countries']);

    $shippingMethod = ShippingMethod::factory()->create([
        'driver' => 'ship-by',
        'data' => [
            'charge_by' => 'weight',
            'weight_unit' => 'g',
        ],
    ]);

    $shippingRate = ShippingRate::factory()->create([
        'shipping_method_id' => $shippingMethod->id,
        'shipping_zone_id' => $shippingZone->id,
    ]);

    $shippingRate->prices()->createMany([
        ['price' => 1000, 'min_quantity' => 1, 'currency_id' => $currency->id],
        ['price' => 500, 'min_quantity' => 500, 'currency_id' => $currency->id],
    ]);

    $variant = ProductVariant::factory()->create([
        'weight_value' => 300.0,
        'weight_unit' => 'g',
    ]);
    $variant->stock = 100;

    Price::factory()->create([
        'price' => 500,
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => $variant->getMorphClass(),
        'priceable_id' => $variant->id,
    ]);

    $cart = Cart::factory()->create(['currency_id' => $currency->id]);
    $cart->lines()->create([
        'purchasable_type' => $variant->getMorphClass(),
        'purchasable_id' => $variant->id,
        'quantity' => 2,
    ]);
    $cart = $cart->calculate();

    $option = (new ShipBy)->resolve(new ShippingOptionRequest(
        shippingRate: $shippingRate,
        cart: $cart,
    ));

    expect($option)->toBeInstanceOf(ShippingOption::class)
        ->and($option->price->value)->toEqual(500);
});

test('weight-based shipping defaults to kg when weight_unit is not set', function () {
    $currency = Currency::factory()->create(['default' => true]);
    TaxClass::factory()->create(['default' => true]);

    $shippingZone = ShippingZone::factory()->create(['type' => 'countries']);

    $shippingMethod = ShippingMethod::factory()->create([
        'driver' => 'ship-by',
        'data' => [
            'charge_by' => 'weight',
        ],
    ]);

    $shippingRate = ShippingRate::factory()->create([
        'shipping_method_id' => $shippingMethod->id,
        'shipping_zone_id' => $shippingZone->id,
    ]);

    $shippingRate->prices()->createMany([
        ['price' => 1000, 'min_quantity' => 1, 'currency_id' => $currency->id],
        ['price' => 600, 'min_quantity' => 600, 'currency_id' => $currency->id],
    ]);

    $variant = ProductVariant::factory()->create([
        'weight_value' => 3.0,
        'weight_unit' => 'kg',
    ]);
    $variant->stock = 100;

    Price::factory()->create([
        'price' => 500,
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => $variant->getMorphClass(),
        'priceable_id' => $variant->id,
    ]);

    $cart = Cart::factory()->create(['currency_id' => $currency->id]);
    $cart->lines()->create([
        'purchasable_type' => $variant->getMorphClass(),
        'purchasable_id' => $variant->id,
        'quantity' => 2,
    ]);
    $cart = $cart->calculate();

    $option = (new ShipBy)->resolve(new ShippingOptionRequest(
        shippingRate: $shippingRate,
        cart: $cart,
    ));

    expect($option)->toBeInstanceOf(ShippingOption::class)
        ->and($option->price->value)->toEqual(600);
});

test('weight_unit configuration persists in shipping method data', function () {
    $shippingMethod = ShippingMethod::factory()->create([
        'driver' => 'ship-by',
        'data' => [
            'charge_by' => 'weight',
            'weight_unit' => 'lbs',
        ],
    ]);

    $shippingMethod->refresh();

    expect($shippingMethod->data['weight_unit'])->toEqual('lbs')
        ->and($shippingMethod->data['charge_by'])->toEqual('weight');
});

test('weight-based shipping converts product units to shipping method weight_unit with x100 scale', function () {
    $currency = Currency::factory()->create(['default' => true]);
    TaxClass::factory()->create(['default' => true]);

    $shippingZone = ShippingZone::factory()->create(['type' => 'countries']);

    $shippingMethod = ShippingMethod::factory()->create([
        'driver' => 'ship-by',
        'data' => [
            'charge_by' => 'weight',
            'weight_unit' => 'kg',
        ],
    ]);

    $shippingRate = ShippingRate::factory()->create([
        'shipping_method_id' => $shippingMethod->id,
        'shipping_zone_id' => $shippingZone->id,
    ]);

    $shippingRate->prices()->createMany([
        ['price' => 1000, 'min_quantity' => 1, 'currency_id' => $currency->id],
        ['price' => 500, 'min_quantity' => 700, 'currency_id' => $currency->id],
    ]);

    $variantKg = ProductVariant::factory()->create(['weight_value' => 2.0, 'weight_unit' => 'kg']);
    $variantKg->stock = 100;
    Price::factory()->create([
        'price' => 500, 'min_quantity' => 1, 'currency_id' => $currency->id,
        'priceable_type' => $variantKg->getMorphClass(), 'priceable_id' => $variantKg->id,
    ]);

    $variantG = ProductVariant::factory()->create(['weight_value' => 5000.0, 'weight_unit' => 'g']);
    $variantG->stock = 100;
    Price::factory()->create([
        'price' => 500, 'min_quantity' => 1, 'currency_id' => $currency->id,
        'priceable_type' => $variantG->getMorphClass(), 'priceable_id' => $variantG->id,
    ]);

    $cart = Cart::factory()->create(['currency_id' => $currency->id]);
    $cart->lines()->createMany([
        ['purchasable_type' => $variantKg->getMorphClass(), 'purchasable_id' => $variantKg->id, 'quantity' => 1],
        ['purchasable_type' => $variantG->getMorphClass(), 'purchasable_id' => $variantG->id, 'quantity' => 1],
    ]);
    $cart = $cart->calculate();

    $option = (new ShipBy)->resolve(new ShippingOptionRequest(
        shippingRate: $shippingRate,
        cart: $cart,
    ));

    expect($option)->toBeInstanceOf(ShippingOption::class)
        ->and($option->price->value)->toEqual(500);
});