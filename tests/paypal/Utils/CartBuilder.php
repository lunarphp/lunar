<?php

namespace Lunar\Tests\Paypal\Utils;

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
    /**
     * Build a calculable cart with a shipping option, both addresses and one line.
     *
     * @param  array<string, mixed>  $cartParams
     * @param  array<string, mixed>  $currencyParams  Overrides for the currency — pass
     *                                                `decimal_places` here to exercise
     *                                                non-two-decimal scaling.
     */
    public static function build(array $cartParams = [], array $currencyParams = [], float $unitPrice = 1.99): Cart
    {
        // Reused when a cart already exists in the test — a second default
        // language or currency collides on the default flag.
        Language::query()->where('default', true)->first()
            ?: Language::factory()->create(['default' => true]);

        $currency = (! $currencyParams ? Currency::query()->where('default', true)->first() : null)
            ?: Currency::factory()->create(array_merge([
                'default' => true,
                // Pinned — faker can roll HUF/JPY/TWD, which PayPal treats as
                // zero-decimal, and the suite's amount assertions assume 2dp.
                'code' => 'USD',
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

        $variant = ProductVariant::factory()->create()->each(function ($variant) use ($currency, $unitPrice) {
            $variant->prices()->create([
                'price' => $unitPrice,
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
