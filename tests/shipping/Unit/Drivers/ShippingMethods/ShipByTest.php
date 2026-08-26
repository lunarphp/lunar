<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
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

    $shippingRate = ShippingRate::factory()
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

    $shippingRate = ShippingRate::factory()
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

    $shippingRate = ShippingRate::factory()
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

test('pricing manager resolves correct tier for weight-based shipping using raw kg min_quantity', function () {
    $currency = Currency::factory()->create(['default' => true]);
    TaxClass::factory()->create(['default' => true]);

    $shippingZone = ShippingZone::factory()->create(['type' => 'countries']);

    $shippingMethod = ShippingMethod::factory()->create([
        'driver' => 'ship-by',
        'data' => ['charge_by' => 'weight'],
    ]);

    $shippingRate = ShippingRate::factory()->create([
        'shipping_method_id' => $shippingMethod->id,
        'shipping_zone_id' => $shippingZone->id,
    ]);

    $shippingRate->prices()->createMany([
        ['price' => 1000, 'min_quantity' => 1,  'currency_id' => $currency->id],
        ['price' => 600,  'min_quantity' => 5,  'currency_id' => $currency->id],
        ['price' => 200,  'min_quantity' => 10, 'currency_id' => $currency->id],
    ]);

    $makeWeightCart = function (float $weightKg) use ($currency): Cart {
        $variant = ProductVariant::factory()->create([
            'weight_value' => $weightKg,
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
            'quantity' => 1,
        ]);

        return $cart->calculate();
    };

    $option = (new ShipBy)->resolve(new ShippingOptionRequest(
        shippingRate: $shippingRate,
        cart: $makeWeightCart(3.0),
    ));
    expect($option)->toBeInstanceOf(ShippingOption::class)
        ->and($option->price->value)->toEqual(1000);

    $option = (new ShipBy)->resolve(new ShippingOptionRequest(
        shippingRate: $shippingRate,
        cart: $makeWeightCart(5.0),
    ));
    expect($option)->toBeInstanceOf(ShippingOption::class)
        ->and($option->price->value)->toEqual(600);

    $option = (new ShipBy)->resolve(new ShippingOptionRequest(
        shippingRate: $shippingRate,
        cart: $makeWeightCart(10.0),
    ));
    expect($option)->toBeInstanceOf(ShippingOption::class)
        ->and($option->price->value)->toEqual(200);
});

test('weight in grams is converted to kg when evaluating weight-based shipping breakpoints', function () {
    $currency = Currency::factory()->create(['default' => true]);
    TaxClass::factory()->create(['default' => true]);

    $shippingZone = ShippingZone::factory()->create(['type' => 'countries']);

    $shippingMethod = ShippingMethod::factory()->create([
        'driver' => 'ship-by',
        'data' => ['charge_by' => 'weight'],
    ]);

    $shippingRate = ShippingRate::factory()->create([
        'shipping_method_id' => $shippingMethod->id,
        'shipping_zone_id' => $shippingZone->id,
    ]);

    $shippingRate->prices()->createMany([
        ['price' => 1000, 'min_quantity' => 1, 'currency_id' => $currency->id],
        ['price' => 500,  'min_quantity' => 5, 'currency_id' => $currency->id],
    ]);

    $variant = ProductVariant::factory()->create([
        'weight_value' => 5000.0,
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
        'quantity' => 1,
    ]);
    $cart = $cart->calculate();

    $option = (new ShipBy)->resolve(new ShippingOptionRequest(
        shippingRate: $shippingRate,
        cart: $cart,
    ));

    expect($option)->toBeInstanceOf(ShippingOption::class)
        ->and($option->price->value)->toEqual(500);
});

test('total cart weight across multiple lines with different units is summed in kg for tier matching', function () {
    $currency = Currency::factory()->create(['default' => true]);
    TaxClass::factory()->create(['default' => true]);

    $shippingZone = ShippingZone::factory()->create(['type' => 'countries']);

    $shippingMethod = ShippingMethod::factory()->create([
        'driver' => 'ship-by',
        'data' => ['charge_by' => 'weight'],
    ]);

    $shippingRate = ShippingRate::factory()->create([
        'shipping_method_id' => $shippingMethod->id,
        'shipping_zone_id' => $shippingZone->id,
    ]);

    $shippingRate->prices()->createMany([
        ['price' => 1000, 'min_quantity' => 1, 'currency_id' => $currency->id],
        ['price' => 400,  'min_quantity' => 5, 'currency_id' => $currency->id],
    ]);

    $variantKg = ProductVariant::factory()->create(['weight_value' => 2.0, 'weight_unit' => 'kg']);
    $variantKg->stock = 100;
    Price::factory()->create([
        'price' => 500, 'min_quantity' => 1, 'currency_id' => $currency->id,
        'priceable_type' => $variantKg->getMorphClass(), 'priceable_id' => $variantKg->id,
    ]);

    $variantG = ProductVariant::factory()->create(['weight_value' => 1500.0, 'weight_unit' => 'g']);
    $variantG->stock = 100;
    Price::factory()->create([
        'price' => 500, 'min_quantity' => 1, 'currency_id' => $currency->id,
        'priceable_type' => $variantG->getMorphClass(), 'priceable_id' => $variantG->id,
    ]);

    $cart = Cart::factory()->create(['currency_id' => $currency->id]);
    $cart->lines()->createMany([
        ['purchasable_type' => $variantKg->getMorphClass(), 'purchasable_id' => $variantKg->id, 'quantity' => 1],
        ['purchasable_type' => $variantG->getMorphClass(),  'purchasable_id' => $variantG->id,  'quantity' => 2],
    ]);
    $cart = $cart->calculate();

    $option = (new ShipBy)->resolve(new ShippingOptionRequest(
        shippingRate: $shippingRate,
        cart: $cart,
    ));

    expect($option)->toBeInstanceOf(ShippingOption::class)
        ->and($option->price->value)->toEqual(400);
});

test('weight tiers are evaluated in the shipping method configured weight_unit', function () {
    $currency = Currency::factory()->create(['default' => true]);
    TaxClass::factory()->create(['default' => true]);

    $shippingZone = ShippingZone::factory()->create(['type' => 'countries']);

    // Grams give merchants sub-kilogram tier precision with raw integer storage.
    $shippingMethod = ShippingMethod::factory()->create([
        'driver' => 'ship-by',
        'data' => ['charge_by' => 'weight'],
        'weight_unit' => 'g',
    ]);

    $shippingRate = ShippingRate::factory()->create([
        'shipping_method_id' => $shippingMethod->id,
        'shipping_zone_id' => $shippingZone->id,
    ]);

    $shippingRate->prices()->createMany([
        ['price' => 1000, 'min_quantity' => 1, 'currency_id' => $currency->id],
        ['price' => 600, 'min_quantity' => 500, 'currency_id' => $currency->id],
        ['price' => 200, 'min_quantity' => 1000, 'currency_id' => $currency->id],
    ]);

    $makeCart = function (array $lines) use ($currency): Cart {
        $cart = Cart::factory()->create(['currency_id' => $currency->id]);

        foreach ($lines as [$weightValue, $weightUnit]) {
            $variant = ProductVariant::factory()->create([
                'weight_value' => $weightValue,
                'weight_unit' => $weightUnit,
            ]);
            $variant->stock = 100;

            Price::factory()->create([
                'price' => 500,
                'min_quantity' => 1,
                'currency_id' => $currency->id,
                'priceable_type' => $variant->getMorphClass(),
                'priceable_id' => $variant->id,
            ]);

            $cart->lines()->create([
                'purchasable_type' => $variant->getMorphClass(),
                'purchasable_id' => $variant->id,
                'quantity' => 1,
            ]);
        }

        return $cart->calculate();
    };

    // 300g cart sits below the 500g tier.
    $option = (new ShipBy)->resolve(new ShippingOptionRequest(
        shippingRate: $shippingRate,
        cart: $makeCart([[0.3, 'kg']]),
    ));
    expect($option)->toBeInstanceOf(ShippingOption::class)
        ->and($option->price->value)->toEqual(1000);

    // A 0.5kg product converts to 500g and matches the 500g tier exactly.
    $option = (new ShipBy)->resolve(new ShippingOptionRequest(
        shippingRate: $shippingRate,
        cart: $makeCart([[0.5, 'kg']]),
    ));
    expect($option)->toBeInstanceOf(ShippingOption::class)
        ->and($option->price->value)->toEqual(600);

    // Mixed units: 0.4kg + 600g = 1000g hits the top tier.
    $option = (new ShipBy)->resolve(new ShippingOptionRequest(
        shippingRate: $shippingRate,
        cart: $makeCart([[0.4, 'kg'], [600.0, 'g']]),
    ));
    expect($option)->toBeInstanceOf(ShippingOption::class)
        ->and($option->price->value)->toEqual(200);
});
