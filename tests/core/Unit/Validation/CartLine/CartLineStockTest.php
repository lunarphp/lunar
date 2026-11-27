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

test('can validate available stock', function (int $stock, int $incoming, int $quantity, bool $continueSelling, bool $sellAgainstIncoming, bool $shouldValidate) {
    $currency = Currency::factory()->create();

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
    ]);

    $purchasable = ProductVariant::factory()->inStock($stock)->create([
        'continue_selling' => $continueSelling,
        'sell_against_incoming' => $sellAgainstIncoming,
    ]);

    $purchasable->forceFill(['stock_incoming' => $incoming])->save();

    $validator = (new CartLineStock)->using(
        cart: $cart,
        purchasable: $purchasable->refresh(),
        quantity: $quantity,
        meta: []
    );

    $expectation = expect(fn () => $validator->validate());

    if ($shouldValidate) {
        $expectation = $expectation->not;
    }

    $expectation->toThrow(CartException::class);
})->with([
    'continue selling, stock below quantity' => [100, 0, 150, true, false, true],
    'continue selling, no stock' => [0, 0, 150, true, false, true],
    'stock bound, enough stock' => [500, 0, 150, false, false, true],
    'stock bound, exactly enough stock' => [150, 0, 150, false, false, true],
    'stock bound, no stock' => [0, 0, 150, false, false, false],
    'stock bound, only incoming' => [0, 150, 150, false, false, false],
    'against incoming, stock plus incoming covers it' => [50, 100, 150, false, true, true],
    'against incoming, one short' => [50, 99, 150, false, true, false],
]);
