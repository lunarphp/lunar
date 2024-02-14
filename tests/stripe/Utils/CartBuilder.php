<?php

namespace Lunar\Tests\Stripe\Utils;

use Lunar\DataTypes\Price;
use Lunar\DataTypes\ShippingOption;
use Lunar\Facades\ShippingManifest;
use Lunar\Models\Cart;
use Lunar\Models\CartAddress;
use Lunar\Models\CartLine;
use Lunar\Models\Currency;
use Lunar\Models\Language;
use Lunar\Models\ProductVariant;
use Lunar\Models\TaxClass;

class CartBuilder
{
    public static function build(array $cartParams = [])
    {
        Language::factory()->create([
            'default' => true,
        ]);

        $currency = Currency::factory()->create([
            'default' => true,
        ]);

        $taxClass = TaxClass::factory()->create();

        $cart = Cart::factory()->create(array_merge([
            'currency_id' => $currency->id,
        ], $cartParams));

        ShippingManifest::addOption(
            new ShippingOption(
                name: 'Basic Delivery',
                description: 'Basic test delivery',
                identifier: 'BASDEL',
                price: new Price(500, $cart->currency, 1),
                taxClass: $taxClass
            )
        );

        CartAddress::factory()->create([
            'cart_id' => $cart->id,
            'shipping_option' => 'BASDEL',
        ]);

        CartAddress::factory()->create([
            'cart_id' => $cart->id,
            'type' => 'billing',
        ]);

        $variant = ProductVariant::factory()->create()->each(function ($variant) use ($currency) {
            $variant->prices()->create([
                'price' => 1.99,
                'currency_id' => $currency->id,
            ]);
        });

        CartLine::factory()->create([
            'cart_id' => $cart->id,
            'purchasable_id' => $variant,
        ]);

        return $cart;
    }
}
