<?php

uses(\Lunar\Tests\Core\TestCase::class)
    ->group('validation.cart_line');

use Lunar\Exceptions\Carts\CartException;
use Lunar\Models\Cart;
use Lunar\Models\Currency;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('can validate available stock', function (int $stock, int $backorder, int $quantity, string $purchasable, bool $shouldValidate = true) {
    $currency = Currency::factory()->create();

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
    ]);

    $purchasable = \Lunar\Models\ProductVariant::factory()->create([
        'stock' => $stock,
        'backorder' => $backorder,
        'purchasable' => $purchasable,
    ]);

    $validator = (new \Lunar\Validation\CartLine\CartLineStock)->using(
        cart: $cart,
        purchasable: $purchasable,
        quantity: $quantity,
        meta: []
    );

    $expectation = expect(fn () => $validator->validate());

    if ($shouldValidate) {
        $expectation = $expectation->not;
    }

    $expectation->toThrow(CartException::class);
})->with([
    [
        100,
        0,
        150,
        'always',
        true,
    ],
    [
        0,
        0,
        150,
        'always',
        true,
    ],
    [
        500,
        0,
        150,
        'in_stock',
        true,
    ],
    [
        150,
        0,
        150,
        'in_stock',
        true,
    ],
    [
        0,
        0,
        150,
        'in_stock',
        false,
    ],
    [
        0,
        150,
        150,
        'in_stock',
        false,
    ],
    [
        0,
        150,
        150,
        'in_stock_or_backorder',
        true,
    ],
]);
