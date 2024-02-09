<?php

uses(\Lunar\Tests\Core\TestCase::class);

use Lunar\Actions\Carts\RemovePurchasable;
use Lunar\Models\Cart;
use Lunar\Models\Currency;
use Lunar\Models\Price;
use Lunar\Models\ProductVariant;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('can remove cart line', function () {
    $currency = Currency::factory()->create();

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
    ]);

    $purchasable = ProductVariant::factory()->create();

    Price::factory()->create([
        'price' => 100,
        'quantity_break' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => get_class($purchasable),
        'priceable_id' => $purchasable->id,
    ]);

    $cart->add($purchasable, 1);

    expect($cart->refresh()->lines)->toHaveCount(1);

    $action = new RemovePurchasable;

    $action->execute($cart, $cart->lines->first()->id);

    expect($cart->refresh()->lines)->toHaveCount(0);
});
