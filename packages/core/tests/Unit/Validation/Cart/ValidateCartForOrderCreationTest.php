<?php

uses(\Lunar\Tests\TestCase::class);
use Lunar\Exceptions\Carts\CartException;
use Lunar\Models\Cart;
use Lunar\Models\CartAddress;
use Lunar\Models\Currency;
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

test('can validate shippable cart', function () {
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
