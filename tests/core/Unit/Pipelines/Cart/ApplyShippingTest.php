<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\DataTypes\Price as PriceDataType;
use Lunar\DataTypes\ShippingOption;
use Lunar\Facades\ShippingManifest;
use Lunar\Models\Cart;
use Lunar\Models\CartAddress;
use Lunar\Models\Country;
use Lunar\Models\Currency;
use Lunar\Models\Price;
use Lunar\Models\ProductVariant;
use Lunar\Models\TaxClass;
use Lunar\Models\TaxRateAmount;
use Lunar\Pipelines\Cart\ApplyShipping;
use Lunar\Pipelines\Cart\CalculateShippingSubTotal;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);

uses(RefreshDatabase::class);

test('can apply empty shipping totals', function () {
    $currency = Currency::factory()->create();

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
    ]);

    $purchasable = ProductVariant::factory()->create();

    Price::factory()->create([
        'price' => 100,
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => $purchasable->getMorphClass(),
        'priceable_id' => $purchasable->id,
    ]);

    $cart->lines()->create([
        'purchasable_type' => $purchasable->getMorphClass(),
        'purchasable_id' => $purchasable->id,
        'quantity' => 1,
    ]);

    expect($cart->shippingTotal)->toBeNull();

    app(ApplyShipping::class)->handle($cart, function ($cart) {
        app(CalculateShippingSubTotal::class)->handle($cart, fn ($cart) => $cart);

        return $cart;
    });

    expect($cart->shippingSubTotal)->toBeInstanceOf(PriceDataType::class);
});

test('can apply shipping totals', function () {
    $currency = Currency::factory()->create();

    $billing = CartAddress::factory()->make([
        'type' => 'billing',
        'country_id' => Country::factory(),
        'first_name' => 'Santa',
        'line_one' => '123 Elf Road',
        'city' => 'Lapland',
        'postcode' => 'BILL',
    ]);

    $shipping = CartAddress::factory()->make([
        'type' => 'shipping',
        'country_id' => Country::factory(),
        'first_name' => 'Santa',
        'line_one' => '123 Elf Road',
        'city' => 'Lapland',
        'postcode' => 'SHIPP',
    ]);

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
    ]);

    $taxClass = TaxClass::factory()->create([
        'name' => 'Foobar',
    ]);

    $taxClass->taxRateAmounts()->create(
        TaxRateAmount::factory()->make([
            'percentage' => 20,
            'tax_class_id' => $taxClass->id,
        ])->toArray()
    );

    $cart->addresses()->createMany([
        $billing->toArray(),
        $shipping->toArray(),
    ]);

    $shippingOption = new ShippingOption(
        name: 'Basic Delivery',
        description: 'Basic Delivery',
        identifier: 'BASDEL',
        price: new PriceDataType(500, $cart->currency, 1),
        taxClass: $taxClass
    );

    ShippingManifest::addOption($shippingOption);

    $cart->shippingAddress->update([
        'shipping_option' => $shippingOption->getIdentifier(),
    ]);

    $purchasable = ProductVariant::factory()->create();

    Price::factory()->create([
        'price' => 100,
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => $purchasable->getMorphClass(),
        'priceable_id' => $purchasable->id,
    ]);

    $cart->lines()->create([
        'purchasable_type' => $purchasable->getMorphClass(),
        'purchasable_id' => $purchasable->id,
        'quantity' => 1,
    ]);

    expect($cart->shippingTotal)->toBeNull();

    app(ApplyShipping::class)->handle($cart, function ($cart) {
        app(CalculateShippingSubTotal::class)->handle($cart, fn ($cart) => $cart);

        return $cart;
    });

    expect($cart->shippingSubTotal)->toBeInstanceOf(PriceDataType::class);
    expect($cart->shippingSubTotal->value)->toEqual(500);
});

test('switching shipping option replaces the breakdown instead of summing', function () {
    $currency = Currency::factory()->create();
    $taxClass = TaxClass::factory()->create();

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
    ]);

    $cart->addresses()->create(
        CartAddress::factory()->make([
            'type' => 'shipping',
            'country_id' => Country::factory(),
        ])->toArray()
    );

    $basic = new ShippingOption(
        name: 'Basic Delivery',
        description: 'Basic Delivery',
        identifier: 'BASDEL',
        price: new PriceDataType(500, $cart->currency, 1),
        taxClass: $taxClass,
    );

    $express = new ShippingOption(
        name: 'Express Delivery',
        description: 'Express Delivery',
        identifier: 'EXPDEL',
        price: new PriceDataType(1500, $cart->currency, 1),
        taxClass: $taxClass,
    );

    ShippingManifest::addOption($basic);
    ShippingManifest::addOption($express);

    $purchasable = ProductVariant::factory()->create();

    Price::factory()->create([
        'price' => 100,
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => $purchasable->getMorphClass(),
        'priceable_id' => $purchasable->id,
    ]);

    $cart->lines()->create([
        'purchasable_type' => $purchasable->getMorphClass(),
        'purchasable_id' => $purchasable->id,
        'quantity' => 1,
    ]);

    $runShippingPipeline = fn ($cart) => app(ApplyShipping::class)->handle(
        $cart,
        fn ($cart) => app(CalculateShippingSubTotal::class)->handle($cart, fn ($cart) => $cart),
    );

    $cart->shippingAddress->update(['shipping_option' => 'BASDEL']);

    $runShippingPipeline($cart);

    expect($cart->shippingSubTotal->value)->toEqual(500);
    expect($cart->shippingBreakdown->items)->toHaveCount(1);

    $cart->shippingAddress->update(['shipping_option' => 'EXPDEL']);

    $runShippingPipeline($cart);

    expect($cart->shippingSubTotal->value)->toEqual(1500);
    expect($cart->shippingBreakdown->items)->toHaveCount(1);
});
