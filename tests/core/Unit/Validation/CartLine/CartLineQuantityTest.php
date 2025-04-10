<?php

uses(\Lunar\Tests\Core\TestCase::class)
    ->group('validation.cart_line');

use Lunar\Exceptions\Carts\CartException;
use Lunar\Models\Cart;
use Lunar\Models\Currency;
use Lunar\Validation\CartLine\CartLineQuantity;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('can validate zero quantity', function () {
    $currency = Currency::factory()->create();

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
    ]);

    $purchasable = \Lunar\Models\ProductVariant::factory()->create();

    $validator = (new CartLineQuantity)->using(
        cart: $cart,
        purchasable: $purchasable,
        quantity: 0,
        meta: []
    );

    expect(fn () => $validator->validate())
        ->toThrow(CartException::class, __('lunar::exceptions.invalid_cart_line_quantity', ['quantity' => 0]));
});

test('can validate excessive quantity', function () {
    $currency = Currency::factory()->create();

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
    ]);

    $purchasable = \Lunar\Models\ProductVariant::factory()->create();

    $quantity = 1000001;

    $validator = (new CartLineQuantity)->using(
        cart: $cart,
        purchasable: $purchasable,
        quantity: $quantity,
        meta: []
    );

    expect(fn () => $validator->validate())
        ->toThrow(CartException::class, __('lunar::exceptions.maximum_cart_line_quantity', ['quantity' => 1000000]));
});

test('can validate minimum quantity', function () {
    $currency = Currency::factory()->create();

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
    ]);

    $purchasable = \Lunar\Models\ProductVariant::factory()->create([
        'min_quantity' => 10,
    ]);

    $quantity = 9;

    $validator = (new CartLineQuantity)->using(
        cart: $cart,
        purchasable: $purchasable,
        quantity: $quantity,
        meta: []
    );

    expect(fn () => $validator->validate())
        ->toThrow(CartException::class, __('lunar::exceptions.minimum_quantity', ['quantity' => $purchasable->min_quantity]));
});

test('can validate quantity increment quantity', function (array $quantities, int $increment) {
    $currency = Currency::factory()->create();

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
    ]);

    $purchasable = \Lunar\Models\ProductVariant::factory()->create([
        'min_quantity' => 1,
        'quantity_increment' => $increment,
    ]);

    foreach ($quantities as $quantity => $outcome) {
        $validator = (new CartLineQuantity)->using(
            cart: $cart,
            purchasable: $purchasable,
            quantity: $quantity,
            meta: []
        );

        if ($outcome == 'fail') {
            expect(fn () => $validator->validate())
                ->toThrow(
                    CartException::class,
                    __('lunar::exceptions.quantity_increment', [
                        'increment' => $purchasable->quantity_increment,
                        'quantity' => $quantity,
                    ])
                );

            continue;
        }

        expect($validator->validate())->toBeTrue();
    }
})->with([
    [
        [
            1 => 'pass',
            2 => 'pass',
            3 => 'pass',
            4 => 'pass',
            5 => 'pass',
            6 => 'pass',
            7 => 'pass',
            8 => 'pass',
            9 => 'pass',
            10 => 'pass',
            20 => 'pass',
            30 => 'pass',
            40 => 'pass',
            50 => 'pass',
            100 => 'pass',
        ],
        1,
    ],
    [
        [
            1 => 'fail',
            2 => 'fail',
            3 => 'fail',
            4 => 'fail',
            5 => 'fail',
            10 => 'pass',
            11 => 'fail',
            15 => 'fail',
            20 => 'pass',
            25 => 'fail',
            30 => 'pass',
            40 => 'pass',
        ],
        10,
    ],
    [
        [
            1 => 'fail',
            2 => 'fail',
            3 => 'fail',
            7 => 'fail',
            14 => 'pass',
            16 => 'fail',
            28 => 'pass',
            36 => 'fail',
            56 => 'pass',
        ],
        14,
    ],
]);

test('can validate from cart line id', function () {
    $currency = Currency::factory()->create();

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
    ]);

    $purchasable = \Lunar\Models\ProductVariant::factory()->create([
        'quantity_increment' => 25,
    ]);

    $cart->lines()->create([
        'purchasable_type' => \Lunar\Models\ProductVariant::class,
        'purchasable_id' => $purchasable->id,
        'quantity' => 50,
    ]);

    $validator = (new CartLineQuantity)->using(
        cart: $cart,
        purchasable: null,
        cartLineId: $cart->lines()->first()->id,
        quantity: 26,
        meta: []
    );

    expect(fn () => $validator->validate())
        ->toThrow(CartException::class);
});
