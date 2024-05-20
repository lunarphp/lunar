<?php

uses(\Lunar\Tests\Core\TestCase::class);

use Lunar\Models\Cart;
use Lunar\Models\Currency;
use Lunar\Models\Price;
use Lunar\Models\ProductVariant;
use Lunar\Pipelines\Cart\CalculateLines;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('can calculate lines', function ($expectedUnitPrice, $incomingUnitPrice, $unitQuantity) {
    $currency = Currency::factory()->create();

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
    ]);

    $purchasable = ProductVariant::factory()->create([
        'unit_quantity' => $unitQuantity,
    ]);

    Price::factory()->create([
        'price' => $incomingUnitPrice,
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => get_class($purchasable),
        'priceable_id' => $purchasable->id,
    ]);

    $cart->lines()->create([
        'purchasable_type' => get_class($purchasable),
        'purchasable_id' => $purchasable->id,
        'quantity' => 1,
    ]);

    $cart = app(CalculateLines::class)->handle($cart, function ($cart) {
        return $cart;
    });

    $cartLine = $cart->lines->first();

    expect($expectedUnitPrice)->toEqual($cartLine->subTotal->unitDecimal);
})->with('providePurchasableData');

dataset('providePurchasableData', function () {
    return [
        'purchasable with 1 unit quantity' => [
            '1.00',
            '100',
            '1',
        ],
        'purchasable with 10 unit quantity' => [
            '0.10',
            '100',
            '10',
        ],
        'purchasable with 100 unit quantity' => [
            '0.01',
            '100',
            '100',
        ],
        'another purchasable with 100 unit quantity' => [
            '0.55',
            '5503',
            '100',
        ],
    ];
});
