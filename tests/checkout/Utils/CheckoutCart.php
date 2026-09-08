<?php

namespace Lunar\Tests\Checkout\Utils;

use Lunar\Checkout\Contracts\CheckoutDriver;
use Lunar\Checkout\Models\CheckoutSession;
use Lunar\Core\Contracts\StorefrontSession;
use Lunar\Core\DataObjects\PriceValue;
use Lunar\Core\DataTypes\ShippingOption;
use Lunar\Core\Facades\CartSession;
use Lunar\Core\Facades\ShippingManifest;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\Channel;
use Lunar\Core\Models\Country;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Price;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\Models\TaxClass;
use Lunar\Core\Models\TaxZone;

/**
 * Builds a cart that can actually become an order, plus the storefront context
 * a real request carries. The shipping option goes straight onto the manifest —
 * the shipping package isn't loaded in these tests, and without an option
 * `canCreateOrder()` fails with `cart_not_orderable`.
 */
class CheckoutCart
{
    public static function orderable(int $unitPrice = 1000, bool $collect = false): Cart
    {
        Language::query()->where('code', 'en')->exists()
            || Language::factory()->create(['code' => 'en', 'default' => true]);
        CustomerGroup::query()->where('default', true)->exists()
            || CustomerGroup::factory()->create(['default' => true]);
        TaxZone::query()->where('default', true)->exists()
            || TaxZone::factory()->create(['default' => true]);

        $channel = Channel::query()->where('handle', 'webstore')->first()
            ?? Channel::factory()->create(['handle' => 'webstore', 'default' => true]);
        $currency = Currency::query()->where('code', 'GBP')->first()
            ?? Currency::factory()->create(['code' => 'GBP', 'default' => true, 'decimal_places' => 2]);
        $taxClass = TaxClass::query()->where('default', true)->first()
            ?? TaxClass::factory()->create(['default' => true]);
        $country = Country::query()->where('iso2', 'GB')->first()
            ?? Country::factory()->create(['iso2' => 'GB']);

        app(StorefrontSession::class)->setChannel($channel)->setCurrency($currency);

        $cart = Cart::factory()->create([
            'channel_id' => $channel->id,
            'currency_id' => $currency->id,
        ]);

        $variant = ProductVariant::factory()->create([
            'tax_class_id' => $taxClass->id,
            'unit_quantity' => 1,
        ]);

        Price::factory()->create([
            'price' => $unitPrice,
            'min_quantity' => 1,
            'currency_id' => $currency->id,
            'priceable_type' => $variant->getMorphClass(),
            'priceable_id' => $variant->id,
        ]);

        $cart->lines()->create([
            'purchasable_type' => $variant->getMorphClass(),
            'purchasable_id' => $variant->id,
            'quantity' => 1,
        ]);

        foreach (['shipping', 'billing'] as $type) {
            $cart->addresses()->create([
                'type' => $type,
                'country_id' => $country->id,
                'first_name' => 'Terry',
                'last_name' => 'Sparks',
                'line_one' => '1 Trade Counter Way',
                'city' => 'London',
                'postcode' => 'SE1 1AA',
            ]);
        }

        CartSession::use($cart);

        $option = new ShippingOption(
            name: $collect ? 'Click & collect' : 'Standard delivery',
            description: $collect ? 'Collect from your local branch' : 'Standard delivery',
            identifier: $collect ? 'collection' : 'standard',
            price: new PriceValue(0, $currency),
            taxClass: $taxClass,
            collect: $collect,
        );

        ShippingManifest::addOption($option);
        $cart->refresh()->calculate()->setShippingOption($option);

        return $cart->refresh()->calculate();
    }

    /**
     * Put one purchasable line on a cart built by another fixture, so it can
     * pass the empty-cart guards without adopting orderable()'s whole world
     * (whose factories are not idempotent against an existing context).
     */
    public static function addLine(Cart $cart, int $unitPrice = 1000): Cart
    {
        // Product creation URL-generates against the default language, and
        // calculating a cart line needs a default tax zone.
        if (! Language::query()->where('default', true)->exists()) {
            Language::factory()->create(['code' => 'en', 'default' => true]);
        }

        if (! TaxZone::query()->where('default', true)->exists()) {
            TaxZone::factory()->create(['default' => true]);
        }

        $taxClass = TaxClass::query()->where('default', true)->first()
            ?? TaxClass::factory()->create(['default' => true]);

        $variant = ProductVariant::factory()->create([
            'tax_class_id' => $taxClass->id,
            'unit_quantity' => 1,
        ]);

        Price::factory()->create([
            'price' => $unitPrice,
            'min_quantity' => 1,
            'currency_id' => $cart->currency_id,
            'priceable_type' => $variant->getMorphClass(),
            'priceable_id' => $variant->id,
        ]);

        $cart->lines()->create([
            'purchasable_type' => $variant->getMorphClass(),
            'purchasable_id' => $variant->id,
            'quantity' => 1,
        ]);

        return $cart->refresh();
    }

    public static function session(Cart $cart): CheckoutSession
    {
        return app(CheckoutDriver::class)->createSession($cart);
    }

    public static function fingerprint(CheckoutSession $session): string
    {
        return app(CheckoutDriver::class)->fingerprint($session);
    }
}
