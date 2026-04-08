<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Models\Cart;
use Lunar\Models\CartAddress;
use Lunar\Models\Country;
use Lunar\Models\Currency;
use Lunar\Models\CustomerGroup;
use Lunar\Models\Price;
use Lunar\Models\ProductVariant;
use Lunar\Models\TaxClass;
use Lunar\Shipping\Facades\Shipping;
use Lunar\Shipping\Models\ShippingMethod;
use Lunar\Shipping\Models\ShippingRate;
use Lunar\Shipping\Models\ShippingZone;
use Lunar\Tests\Shipping\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

/**
 * Build the scaffolding common to all weight tests and return the calculated cart + rate.
 */
function makeWeightScenario(
    float $lineWeight,
    string $lineWeightUnit,
    int $lineQuantity,
    ?float $minWeight,
    ?float $maxWeight,
    ?string $methodWeightUnit,
): array {
    $currency = Currency::factory()->create(['default' => true]);
    TaxClass::factory()->create(['default' => true]);
    $customerGroup = CustomerGroup::factory()->create(['default' => true]);
    $country = Country::factory()->create();

    $zone = ShippingZone::factory()->create(['type' => 'countries']);
    $zone->countries()->attach($country);

    $method = ShippingMethod::factory()->create([
        'driver' => 'ship-by',
        'min_weight' => $minWeight,
        'max_weight' => $maxWeight,
        'weight_unit' => $methodWeightUnit,
        'data' => [],
    ]);

    $method->customerGroups()->sync([
        $customerGroup->id => ['enabled' => true, 'visible' => true, 'starts_at' => now(), 'ends_at' => null],
    ]);

    $rate = ShippingRate::factory()->create([
        'shipping_method_id' => $method->id,
        'shipping_zone_id' => $zone->id,
    ]);

    $rate->prices()->create([
        'price' => 500,
        'min_quantity' => 1,
        'currency_id' => $currency->id,
    ]);

    $variant = ProductVariant::factory()->create([
        'weight_value' => $lineWeight,
        'weight_unit' => $lineWeightUnit,
    ]);
    $variant->stock = 100;

    Price::factory()->create([
        'price' => 1000,
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => $variant->getMorphClass(),
        'priceable_id' => $variant->id,
    ]);

    $cart = Cart::factory()->create(['currency_id' => $currency->id]);

    $cart->lines()->create([
        'purchasable_type' => $variant->getMorphClass(),
        'purchasable_id' => $variant->id,
        'quantity' => $lineQuantity,
    ]);

    $cart->shippingAddress()->create(
        CartAddress::factory()->make([
            'country_id' => $country->id,
            'state' => null,
        ])->toArray()
    );

    return [
        'cart' => $cart->refresh()->calculate(),
        'rate' => $rate,
    ];
}

// ── No constraints ────────────────────────────────────────────────────────────

test('accepts any cart weight when no weight constraints are set', function () {
    ['cart' => $cart, 'rate' => $rate] = makeWeightScenario(
        lineWeight: 50.0,
        lineWeightUnit: 'kg',
        lineQuantity: 3,
        minWeight: null,
        maxWeight: null,
        methodWeightUnit: null,
    );

    $rates = Shipping::shippingRates($cart)->get();

    expect($rates)->toHaveCount(1)
        ->and($rates->first()->id)->toBe($rate->id);
});

// ── Minimum weight ────────────────────────────────────────────────────────────

test('rejects cart below the minimum weight', function () {
    // 1 × 4 kg = 4 kg; min = 5 kg → rejected
    ['cart' => $cart] = makeWeightScenario(
        lineWeight: 4.0,
        lineWeightUnit: 'kg',
        lineQuantity: 1,
        minWeight: 5.0,
        maxWeight: null,
        methodWeightUnit: 'kg',
    );

    expect(Shipping::shippingRates($cart)->get())->toBeEmpty();
});

test('accepts cart exactly at the minimum weight', function () {
    // 1 × 5 kg = 5 kg; min = 5 kg → accepted
    ['cart' => $cart, 'rate' => $rate] = makeWeightScenario(
        lineWeight: 5.0,
        lineWeightUnit: 'kg',
        lineQuantity: 1,
        minWeight: 5.0,
        maxWeight: null,
        methodWeightUnit: 'kg',
    );

    $rates = Shipping::shippingRates($cart)->get();

    expect($rates)->toHaveCount(1)
        ->and($rates->first()->id)->toBe($rate->id);
});

test('accepts cart above the minimum weight', function () {
    // 2 × 5 kg = 10 kg; min = 5 kg → accepted
    ['cart' => $cart, 'rate' => $rate] = makeWeightScenario(
        lineWeight: 5.0,
        lineWeightUnit: 'kg',
        lineQuantity: 2,
        minWeight: 5.0,
        maxWeight: null,
        methodWeightUnit: 'kg',
    );

    $rates = Shipping::shippingRates($cart)->get();

    expect($rates)->toHaveCount(1)
        ->and($rates->first()->id)->toBe($rate->id);
});

// ── Maximum weight ────────────────────────────────────────────────────────────

test('rejects cart above the maximum weight', function () {
    // 1 × 11 kg = 11 kg; max = 10 kg → rejected
    ['cart' => $cart] = makeWeightScenario(
        lineWeight: 11.0,
        lineWeightUnit: 'kg',
        lineQuantity: 1,
        minWeight: null,
        maxWeight: 10.0,
        methodWeightUnit: 'kg',
    );

    expect(Shipping::shippingRates($cart)->get())->toBeEmpty();
});

test('accepts cart exactly at the maximum weight', function () {
    // 2 × 5 kg = 10 kg; max = 10 kg → accepted
    ['cart' => $cart, 'rate' => $rate] = makeWeightScenario(
        lineWeight: 5.0,
        lineWeightUnit: 'kg',
        lineQuantity: 2,
        minWeight: null,
        maxWeight: 10.0,
        methodWeightUnit: 'kg',
    );

    $rates = Shipping::shippingRates($cart)->get();

    expect($rates)->toHaveCount(1)
        ->and($rates->first()->id)->toBe($rate->id);
});

test('accepts cart below the maximum weight', function () {
    // 1 × 3 kg = 3 kg; max = 10 kg → accepted
    ['cart' => $cart, 'rate' => $rate] = makeWeightScenario(
        lineWeight: 3.0,
        lineWeightUnit: 'kg',
        lineQuantity: 1,
        minWeight: null,
        maxWeight: 10.0,
        methodWeightUnit: 'kg',
    );

    $rates = Shipping::shippingRates($cart)->get();

    expect($rates)->toHaveCount(1)
        ->and($rates->first()->id)->toBe($rate->id);
});

// ── Both constraints ──────────────────────────────────────────────────────────

test('accepts cart weight within the min/max window', function () {
    // 3 × 3 kg = 9 kg; min = 5 kg, max = 15 kg → accepted
    ['cart' => $cart, 'rate' => $rate] = makeWeightScenario(
        lineWeight: 3.0,
        lineWeightUnit: 'kg',
        lineQuantity: 3,
        minWeight: 5.0,
        maxWeight: 15.0,
        methodWeightUnit: 'kg',
    );

    $rates = Shipping::shippingRates($cart)->get();

    expect($rates)->toHaveCount(1)
        ->and($rates->first()->id)->toBe($rate->id);
});

test('rejects cart weight below the min/max window', function () {
    // 1 × 2 kg = 2 kg; min = 5 kg, max = 15 kg → rejected
    ['cart' => $cart] = makeWeightScenario(
        lineWeight: 2.0,
        lineWeightUnit: 'kg',
        lineQuantity: 1,
        minWeight: 5.0,
        maxWeight: 15.0,
        methodWeightUnit: 'kg',
    );

    expect(Shipping::shippingRates($cart)->get())->toBeEmpty();
});

test('rejects cart weight above the min/max window', function () {
    // 4 × 5 kg = 20 kg; min = 5 kg, max = 15 kg → rejected
    ['cart' => $cart] = makeWeightScenario(
        lineWeight: 5.0,
        lineWeightUnit: 'kg',
        lineQuantity: 4,
        minWeight: 5.0,
        maxWeight: 15.0,
        methodWeightUnit: 'kg',
    );

    expect(Shipping::shippingRates($cart)->get())->toBeEmpty();
});

// ── Unit conversion ───────────────────────────────────────────────────────────

test('converts product weight in grams to kg when method uses kg', function () {
    // 1 × 2000 g = 2000 g = 2 kg; min = 1 kg, max = 3 kg → accepted
    ['cart' => $cart, 'rate' => $rate] = makeWeightScenario(
        lineWeight: 2000.0,
        lineWeightUnit: 'g',
        lineQuantity: 1,
        minWeight: 1.0,
        maxWeight: 3.0,
        methodWeightUnit: 'kg',
    );

    $rates = Shipping::shippingRates($cart)->get();

    expect($rates)->toHaveCount(1)
        ->and($rates->first()->id)->toBe($rate->id);
});

test('converts product weight in kg to grams when method uses g', function () {
    // 1 × 2 kg = 2000 g; min = 1500 g, max = 2500 g → accepted
    ['cart' => $cart, 'rate' => $rate] = makeWeightScenario(
        lineWeight: 2.0,
        lineWeightUnit: 'kg',
        lineQuantity: 1,
        minWeight: 1500.0,
        maxWeight: 2500.0,
        methodWeightUnit: 'g',
    );

    $rates = Shipping::shippingRates($cart)->get();

    expect($rates)->toHaveCount(1)
        ->and($rates->first()->id)->toBe($rate->id);
});

test('rejects after conversion when converted weight falls outside the window', function () {
    // 1 × 0.5 kg = 500 g; max = 400 g → rejected
    ['cart' => $cart] = makeWeightScenario(
        lineWeight: 0.5,
        lineWeightUnit: 'kg',
        lineQuantity: 1,
        minWeight: null,
        maxWeight: 400.0,
        methodWeightUnit: 'g',
    );

    expect(Shipping::shippingRates($cart)->get())->toBeEmpty();
});

// ── Multi-line weight calculation ─────────────────────────────────────────────

test('sums weight across multiple lines with mixed units', function () {
    $currency = Currency::factory()->create(['default' => true]);
    TaxClass::factory()->create(['default' => true]);
    $customerGroup = CustomerGroup::factory()->create(['default' => true]);
    $country = Country::factory()->create();

    $zone = ShippingZone::factory()->create(['type' => 'countries']);
    $zone->countries()->attach($country);

    // Method: min = 3 kg — two lines totalling exactly 3 kg
    $method = ShippingMethod::factory()->create([
        'driver' => 'ship-by',
        'min_weight' => 3.0,
        'max_weight' => null,
        'weight_unit' => 'kg',
        'data' => [],
    ]);

    $method->customerGroups()->sync([
        $customerGroup->id => ['enabled' => true, 'visible' => true, 'starts_at' => now(), 'ends_at' => null],
    ]);

    $rate = ShippingRate::factory()->create([
        'shipping_method_id' => $method->id,
        'shipping_zone_id' => $zone->id,
    ]);

    $rate->prices()->create(['price' => 500, 'min_quantity' => 1, 'currency_id' => $currency->id]);

    $cart = Cart::factory()->create(['currency_id' => $currency->id]);

    // Line 1: 2 × 1 kg = 2 kg
    $v1 = ProductVariant::factory()->create(['weight_value' => 1.0, 'weight_unit' => 'kg']);
    $v1->stock = 100;
    Price::factory()->create(['price' => 500, 'min_quantity' => 1, 'currency_id' => $currency->id, 'priceable_type' => $v1->getMorphClass(), 'priceable_id' => $v1->id]);
    $cart->lines()->create(['purchasable_type' => $v1->getMorphClass(), 'purchasable_id' => $v1->id, 'quantity' => 2]);

    // Line 2: 1 × 1000 g = 1 kg → total 3 kg, exactly at minimum
    $v2 = ProductVariant::factory()->create(['weight_value' => 1000.0, 'weight_unit' => 'g']);
    $v2->stock = 100;
    Price::factory()->create(['price' => 300, 'min_quantity' => 1, 'currency_id' => $currency->id, 'priceable_type' => $v2->getMorphClass(), 'priceable_id' => $v2->id]);
    $cart->lines()->create(['purchasable_type' => $v2->getMorphClass(), 'purchasable_id' => $v2->id, 'quantity' => 1]);

    $cart->shippingAddress()->create(
        CartAddress::factory()->make(['country_id' => $country->id, 'state' => null])->toArray()
    );

    $rates = Shipping::shippingRates($cart->refresh()->calculate())->get();

    expect($rates)->toHaveCount(1)
        ->and($rates->first()->id)->toBe($rate->id);
});
