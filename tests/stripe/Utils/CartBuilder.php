<?php

namespace Lunar\Tests\Stripe\Utils;

use Lunar\Core\DataObjects\PriceValue;
use Lunar\Core\DataTypes\ShippingOption;
use Lunar\Core\Facades\ShippingManifest;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\CartAddress;
use Lunar\Core\Models\CartLine;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\Models\TaxClass;

class CartBuilder
{
    public static function build(array $cartParams = [], array $currencyParams = [])
    {
        Language::factory()->create([
            'default' => true,
        ]);

        $currency = Currency::factory()->create(array_merge([
            'default' => true,
        ], $currencyParams));

        $taxClass = TaxClass::factory()->create();

        $cart = Cart::factory()->create(array_merge([
            'currency_id' => $currency->id,
        ], $cartParams));

        ShippingManifest::addOption(
            new ShippingOption(
                name: 'Basic Delivery',
                description: 'Basic test delivery',
                identifier: 'BASDEL',
                price: new PriceValue(500, $cart->currency),
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
