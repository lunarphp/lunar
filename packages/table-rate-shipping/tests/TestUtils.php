<?php

namespace Lunar\Shipping\Tests;

use Lunar\Models\Cart;
use Lunar\Models\Currency;
use Lunar\Models\Price;
use Lunar\Models\ProductVariant;

trait TestUtils
{
    public function createCart($currency = null, $price = 100, $quantity = 1)
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
            'tier' => 1,
            'currency_id' => $currency->id,
            'priceable_type' => get_class($purchasable),
            'priceable_id' => $purchasable->id,
        ]);

        $cart->lines()->create([
            'purchasable_type' => get_class($purchasable),
            'purchasable_id' => $purchasable->id,
            'quantity' => $quantity,
        ]);

        $this->assertNull($cart->total);
        $this->assertNull($cart->taxTotal);
        $this->assertNull($cart->subTotal);

        return $cart->calculate();
    }
}
