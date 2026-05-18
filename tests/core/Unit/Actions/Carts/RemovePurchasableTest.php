<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Actions\Carts\RemovePurchasable;
use Lunar\Models\Cart;
use Lunar\Models\Currency;
use Lunar\Models\Price;
use Lunar\Models\ProductVariant;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);

uses(RefreshDatabase::class);

test('can remove cart line', function () {
    $currency = Currency::factory()->create();

    $cart = Cart::factory()->create([
        'currency_id' => $currency->id,
    ]);

    $purchasable = ProductVariant::factory()->create([
        'stock' => 1,
    ]);

    Price::factory()->create([
        'price' => 100,
        'min_quantity' => 1,
        'currency_id' => $currency->id,
        'priceable_type' => $purchasable->getMorphClass(),
        'priceable_id' => $purchasable->id,
    ]);

    $cart->add($purchasable, 1);

    expect($cart->refresh()->lines)->toHaveCount(1);

    $action = new RemovePurchasable;

    $action->execute($cart, $cart->lines->first()->id);

    expect($cart->refresh()->lines)->toHaveCount(0);
});
