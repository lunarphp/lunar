<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Exceptions\Carts\CartException;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\Validation\CartLine\CartLineStock;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class)
    ->group('validation.cart_line');
uses(RefreshDatabase::class);

test('can validate available stock', function (int $stock, int $backorder, int $quantity, string $purchasable, bool $shouldValidate = true) {
    $currency = Currency::factory()->create();

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
    ]);

    $purchasable = ProductVariant::factory()->inStock($stock)->create([
        'backorder' => $backorder,
        'purchasable' => $purchasable,
    ]);

    $validator = (new CartLineStock)->using(
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
