<?php

uses(\Lunar\Tests\Core\TestCase::class)
    ->group('validation.cart_line');

use Lunar\Exceptions\Carts\CartException;
use Lunar\Models\Cart;
use Lunar\Models\Currency;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('can validate available stock', function (int $stock, int $quantity, string $purchasable, bool $shouldValidate = true) {
    $currency = Currency::factory()->create();

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
    ]);

    $purchasable = \Lunar\Models\ProductVariant::factory()->create([
        'stock' => $stock,
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
        'quantity' => 150,
        'purchasable' => 'always',
        'shouldValidate' => true,
    ],
    'Purchasability: "always" without stock' => [
        'stock' => 0,
        'quantity' => 150,
        'purchasable' => 'always',
        'shouldValidate' => true,
    ],
    'Purchasability: "in_stock" with sufficient stock level' => [
        'stock' => 0,
        'quantity' => 150,
        'purchasable' => 'always',
        'shouldValidate' => true,
    ],
])->group('blueberry');
