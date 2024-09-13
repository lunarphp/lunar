<?php

uses(\Lunar\Tests\Core\TestCase::class);

use Lunar\DataTypes\Price;
use Lunar\DataTypes\ShippingOption;
use Lunar\Exceptions\Carts\CartException;
use Lunar\Facades\ShippingManifest;
use Lunar\Models\Cart;
use Lunar\Models\CartAddress;
use Lunar\Models\Currency;
use Lunar\Models\ProductVariant;
use Lunar\Models\TaxClass;
use Lunar\Validation\Cart\ValidateCartForOrderCreation;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('can validate missing billing address', function () {
    $currency = Currency::factory()->create();

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
    ]);

    $validator = (new ValidateCartForOrderCreation)->using(
        cart: $cart
    );

    $this->expectException(CartException::class);
    $this->expectExceptionMessage(__('lunar::exceptions.carts.billing_missing'));

    $validator->validate();
});

test('can validate populated billing address', function () {
    $currency = Currency::factory()->create();

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
    ]);

    $validator = (new ValidateCartForOrderCreation)->using(
        cart: $cart
    );

    CartAddress::factory()->create([
        'type' => 'billing',
        'cart_id' => $cart->id,
    ]);

    expect($validator->validate())->toBeTrue();
});

test('can validate partial billing address', function () {
    $currency = Currency::factory()->create();

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
    ]);

    $validator = (new ValidateCartForOrderCreation)->using(
        cart: $cart
    );

    CartAddress::factory()->create([
        'type' => 'billing',
        'cart_id' => $cart->id,
        'first_name' => null,
        'line_one' => null,
        'city' => null,
        'postcode' => null,
        'country_id' => null,
    ]);

    try {
        $validator->validate();
    } catch (CartException $e) {
        $errors = $e->errors();

        expect($errors->has([
            'country_id',
            'first_name',
            'line_one',
            'city',
            'postcode',
        ]))->toBeTrue();
    }
});

test('can validate missing shipping option', function () {
    $currency = Currency::factory()->create();

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
    ]);

    $purchasable = ProductVariant::factory()->create([
        'shippable' => true,
    ]);

    \Lunar\Models\Price::factory()->create([
        'currency_id' => $currency->id,
        'priceable_id' => $purchasable->id,
        'priceable_type' => $purchasable->getMorphClass(),
        'price' => 500,
    ]);

    $cart->lines()->create([
        'purchasable_type' => $purchasable->getMorphClass(),
        'purchasable_id' => $purchasable->id,
        'quantity' => 1,
    ]);

    $validator = (new ValidateCartForOrderCreation)->using(
        cart: $cart
    );

    CartAddress::factory()->create([
        'type' => 'billing',
        'cart_id' => $cart->id,
    ]);

    expect(fn () => $validator->validate())->toThrow(CartException::class);

});

test('can validate collection with partial shipping address', function () {
    $currency = Currency::factory()->create();
    $taxClass = TaxClass::factory()->create();

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
    ]);

    $purchasable = ProductVariant::factory()->create([
        'shippable' => true,
    ]);

    \Lunar\Models\Price::factory()->create([
        'currency_id' => $currency->id,
        'priceable_id' => $purchasable->id,
        'priceable_type' => $purchasable->getMorphClass(),
        'price' => 500,
    ]);

    $cart->lines()->create([
        'purchasable_type' => $purchasable->getMorphClass(),
        'purchasable_id' => $purchasable->id,
        'quantity' => 1,
    ]);

    $shippingOption = new ShippingOption(
        name: 'Collection',
        description: 'Collection',
        identifier: 'COLLECT',
        price: new Price(0, $cart->currency, 1),
        taxClass: $taxClass,
        collect: true
    );

    ShippingManifest::addOption($shippingOption);

    CartAddress::factory()->create([
        'type' => 'shipping',
        'cart_id' => $cart->id,
        'first_name' => null,
        'line_one' => null,
        'city' => null,
        'postcode' => null,
        'country_id' => null,
        'shipping_option' => $shippingOption->getIdentifier(),
    ]);

    CartAddress::factory()->create([
        'type' => 'billing',
        'cart_id' => $cart->id,
    ]);

    $validator = (new ValidateCartForOrderCreation)->using(
        cart: $cart
    );

    expect($validator->validate())->toBeTrue();
});

test('can validate delivery with partial shipping address', function () {
    $currency = Currency::factory()->create();
    $taxClass = TaxClass::factory()->create();

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
    ]);

    $purchasable = ProductVariant::factory()->create([
        'shippable' => true,
    ]);

    \Lunar\Models\Price::factory()->create([
        'currency_id' => $currency->id,
        'priceable_id' => $purchasable->id,
        'priceable_type' => $purchasable->getMorphClass(),
        'price' => 500,
    ]);

    $cart->lines()->create([
        'purchasable_type' => $purchasable->getMorphClass(),
        'purchasable_id' => $purchasable->id,
        'quantity' => 1,
    ]);

    $shippingOption = new ShippingOption(
        name: 'Basic Delivery',
        description: 'Basic Delivery',
        identifier: 'BASDEL',
        price: new Price(500, $cart->currency, 1),
        taxClass: $taxClass
    );

    ShippingManifest::addOption($shippingOption);

    CartAddress::factory()->create([
        'type' => 'shipping',
        'cart_id' => $cart->id,
        'first_name' => null,
        'line_one' => null,
        'city' => null,
        'postcode' => null,
        'country_id' => null,
        'shipping_option' => $shippingOption->getIdentifier(),
    ]);

    CartAddress::factory()->create([
        'type' => 'billing',
        'cart_id' => $cart->id,
    ]);

    $validator = (new ValidateCartForOrderCreation)->using(
        cart: $cart
    );

    try {
        $validator->validate();
    } catch (CartException $e) {
        $errors = $e->errors();

        expect($errors->has([
            'country_id',
            'first_name',
            'line_one',
            'city',
            'postcode',
        ]))->toBeTrue();
    }
});

test('can validate delivery with populated shipping address', function () {
    $currency = Currency::factory()->create();
    $taxClass = TaxClass::factory()->create();

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
    ]);

    $purchasable = ProductVariant::factory()->create([
        'shippable' => true,
    ]);

    \Lunar\Models\Price::factory()->create([
        'currency_id' => $currency->id,
        'priceable_id' => $purchasable->id,
        'priceable_type' => $purchasable->getMorphClass(),
        'price' => 500,
    ]);

    $cart->lines()->create([
        'purchasable_type' => $purchasable->getMorphClass(),
        'purchasable_id' => $purchasable->id,
        'quantity' => 1,
    ]);

    $shippingOption = new ShippingOption(
        name: 'Basic Delivery',
        description: 'Basic Delivery',
        identifier: 'BASDEL',
        price: new Price(500, $cart->currency, 1),
        taxClass: $taxClass
    );

    ShippingManifest::addOption($shippingOption);

    CartAddress::factory()->create([
        'type' => 'shipping',
        'cart_id' => $cart->id,
        'shipping_option' => $shippingOption->getIdentifier(),
    ]);

    CartAddress::factory()->create([
        'type' => 'billing',
        'cart_id' => $cart->id,
    ]);

    $validator = (new ValidateCartForOrderCreation)->using(
        cart: $cart
    );

    expect($validator->validate())->toBeTrue();
});
