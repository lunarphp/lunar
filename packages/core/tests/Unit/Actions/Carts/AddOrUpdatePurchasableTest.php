<?php

uses(\Lunar\Tests\TestCase::class);
use Lunar\Actions\Carts\AddOrUpdatePurchasable;
use Lunar\Exceptions\InvalidCartLineQuantityException;
use Lunar\Models\Cart;
use Lunar\Models\CartLine;
use Lunar\Models\Currency;
use Lunar\Models\Price;
use Lunar\Models\ProductVariant;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('can add cart lines', function () {
    $currency = Currency::factory()->create();

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
    ]);

    $purchasable = ProductVariant::factory()->create();

    Price::factory()->create([
        'price' => 100,
        'tier' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => get_class($purchasable),
        'priceable_id' => $purchasable->id,
    ]);

    expect($cart->lines)->toHaveCount(0);

    $action = new AddOrUpdatePurchasable;

    $action->execute($cart, $purchasable, 1);

    expect($cart->refresh()->lines)->toHaveCount(1);
});

test('cannot add zero quantity line', function () {
    $currency = Currency::factory()->create();

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
    ]);

    $purchasable = ProductVariant::factory()->create();

    Price::factory()->create([
        'price' => 100,
        'tier' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => get_class($purchasable),
        'priceable_id' => $purchasable->id,
    ]);

    expect($cart->lines)->toHaveCount(0);

    $this->expectException(InvalidCartLineQuantityException::class);

    $action = new AddOrUpdatePurchasable;

    $action->execute($cart, $purchasable, 0);
});

test('can update existing cart line', function () {
    $currency = Currency::factory()->create();

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
    ]);

    $purchasable = ProductVariant::factory()->create();

    Price::factory()->create([
        'price' => 100,
        'tier' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => get_class($purchasable),
        'priceable_id' => $purchasable->id,
    ]);

    $action = new AddOrUpdatePurchasable;

    expect($cart->lines)->toHaveCount(0);

    $action->execute($cart, $purchasable, 1);

    expect($cart->refresh()->lines)->toHaveCount(1);

    $action->execute($cart, $purchasable, 1);

    expect($cart->refresh()->lines)->toHaveCount(1);

    $this->assertDatabaseHas((new CartLine())->getTable(), [
        'cart_id' => $cart->id,
        'quantity' => 2,
    ]);
});
