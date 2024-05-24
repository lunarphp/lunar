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

    $validator = (new \Lunar\Validation\CartLine\CartLineStock())->using(
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
    'Purchasability: "always" with sufficient stock' => [
        'stock' => 100,
        'backorder' => 0,
        'quantity' => 150,
        'purchasable' => 'always',
        'shouldValidate' => true,
    ],
    'Purchasability: "always" without stock' => [
        'stock' => 0,
        'backorder' => 0,
        'quantity' => 150,
        'purchasable' => 'always',
        'shouldValidate' => true,
    ],
    'Purchasability: "in_stock" with sufficient stock level' => [
        'stock' => 500,
        'backorder' => 0,
        'quantity' => 150,
        'purchasable' => 'in_stock',
        'shouldValidate' => true,
    ],
    'Purchasability: "in_stock" with exact stock level' => [
        'stock' => 150,
        'backorder' => 0,
        'quantity' => 150,
        'purchasable' => 'in_stock',
        'shouldValidate' => true,
    ],
    'Purchasability: "in_stock" with insufficient stock level' => [
        'stock' => 0,
        'backorder' => 0,
        'quantity' => 150,
        'purchasable' => 'in_stock',
        'shouldValidate' => false,
    ],
    'Purchasability: "in_stock" with insufficient stock level and backorder' => [
        'stock' => 0,
        'backorder' => 150,
        'quantity' => 150,
        'purchasable' => 'in_stock',
        'shouldValidate' => false,
    ],
    'Purchasability: "in_stock_or_backorder" with insufficient stock level and backorder' => [
        'stock' => 0,
        'backorder' => 150,
        'quantity' => 150,
        'purchasable' => 'in_stock_or_backorder',
        'shouldValidate' => true,
    ],
])->group('blueberry');
