<?php

namespace Lunar\Tests\Shipping;

use Lunar\Models\Cart;
use Lunar\Models\Currency;
use Lunar\Models\Price;
use Lunar\Models\ProductVariant;

trait TestUtils
{
    public function createCart($currency = null, $price = 100, $quantity = 1, $calculate = true)
    {
        if (! $currency) {
            $currency = Currency::factory()->create([
                'default' => true,
            ]);
        }

        $cart = Cart::factory()->create([
            'currency_id' => $currency->id,
        ]);

        $purchasable = ProductVariant::factory()->create();
        $purchasable->stock = 100;

        Price::factory()->create([
            'price' => $price,
            'min_quantity' => 1,
            'currency_id' => $currency->id,
            'priceable_type' => get_class($purchasable),
            'priceable_id' => $purchasable->id,
        ]);

        $cart->lines()->create([
            'purchasable_type' => get_class($purchasable),
            'purchasable_id' => $purchasable->id,
            'quantity' => $quantity,
        ]);

        expect($cart->total)->toBeNull()
            ->and($cart->taxTotal)->toBeNull()
            ->and($cart->subTotal)->toBeNull();

        return $calculate ? $cart->calculate() : $cart;
    }
}
