<?php

namespace Lunar\Tests\Shipping;

use Lunar\Core\Models\Cart;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Price;
use Lunar\Core\Models\ProductVariant;

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

        Price::factory()->create([
            'price' => $price,
            'min_quantity' => 1,
            'currency_id' => $currency->id,
            'priceable_type' => $purchasable->getMorphClass(),
            'priceable_id' => $purchasable->id,
        ]);

        $cart->lines()->create([
            'purchasable_type' => $purchasable->getMorphClass(),
            'purchasable_id' => $purchasable->id,
            'quantity' => $quantity,
        ]);

        expect($cart->total)->toBeNull()
            ->and($cart->taxTotal)->toBeNull()
            ->and($cart->subTotal)->toBeNull();

        return $calculate ? $cart->calculate() : $cart;
    }
}
