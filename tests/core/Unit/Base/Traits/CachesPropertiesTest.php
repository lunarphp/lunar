<?php

uses(\Lunar\Tests\Core\TestCase::class);

use Lunar\DataTypes\Price as DataTypesPrice;
use Lunar\Models\Cart;
use Lunar\Models\Currency;
use Lunar\Models\Price;
use Lunar\Models\ProductVariant;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('can cache model properties', function () {
    $currency = Currency::factory()->create();

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
    ]);

    $purchasable = ProductVariant::factory()->create();

    Price::factory()->create([
        'price' => 100,
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

    $cart = $cart->calculate();

    expect($cart->subTotal)->toBeInstanceOf(DataTypesPrice::class);
    expect($cart->subTotal->value)->toEqual(100);
    expect($cart->total)->toBeInstanceOf(DataTypesPrice::class);
    expect($cart->total->value)->toEqual(120);
    expect($cart->taxTotal)->toBeInstanceOf(DataTypesPrice::class);
    expect($cart->taxTotal->value)->toEqual(20);

    // When now fetching from the database it should automatically be hydrated...
    $cart = Cart::find($cart->id);

    expect($cart->subTotal)->toBeInstanceOf(DataTypesPrice::class);
    expect($cart->subTotal->value)->toEqual(100);
    expect($cart->total)->toBeInstanceOf(DataTypesPrice::class);
    expect($cart->total->value)->toEqual(120);
    expect($cart->taxTotal)->toBeInstanceOf(DataTypesPrice::class);
    expect($cart->taxTotal->value)->toEqual(20);
});
