<?php

namespace GetCandy\Discounts\Tests;

use GetCandy\Managers\CartManager;
use GetCandy\Models\Cart;
use GetCandy\Models\Currency;
use GetCandy\Models\Price;
use GetCandy\Models\ProductVariant;

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

        Price::factory()->create([
            'price'          => $price,
            'tier'           => 1,
            'currency_id'    => $currency->id,
            'priceable_type' => get_class($purchasable),
            'priceable_id'   => $purchasable->id,
        ]);

        $cart->lines()->create([
            'purchasable_type' => get_class($purchasable),
            'purchasable_id'   => $purchasable->id,
            'quantity'         => $quantity,
        ]);

        $manager = new CartManager($cart);

        $this->assertNull($cart->total);
        $this->assertNull($cart->taxTotal);
        $this->assertNull($cart->subTotal);

        return $manager->getCart();
    }
}
