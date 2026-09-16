<?php

namespace Lunar\Tests\Bundles\Support;

use Lunar\Core\DataObjects\PriceValue;
use Lunar\Core\DataTypes\ShippingOption;
use Lunar\Core\Facades\ShippingManifest;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\CartAddress;
use Lunar\Core\Models\Country;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\CustomerGroup;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Location;
use Lunar\Core\Models\Price;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\Models\TaxClass;

/**
 * Store scaffolding shared by the bundle feature tests.
 */
class Catalogue
{
    /**
     * The defaults every pricing and checkout path relies on.
     */
    public static function store(): Currency
    {
        Language::factory()->create(['default' => true, 'code' => 'en']);
        CustomerGroup::factory()->create(['default' => true]);
        TaxClass::factory()->create(['default' => true]);
        Location::factory()->create(['default' => true]);

        return Currency::factory()->create(['default' => true, 'code' => 'GBP', 'decimal_places' => 2]);
    }

    /**
     * A priced, stocked, shippable variant.
     */
    public static function variant(Currency $currency, int $price, ?int $stock = null, array $attributes = []): ProductVariant
    {
        $factory = ProductVariant::factory();

        if ($stock !== null) {
            $factory = $factory->inStock($stock);
        }

        $variant = $factory->create($attributes);

        Price::factory()->create([
            'price' => $price,
            'list_price' => null,
            'min_quantity' => 1,
            'currency_id' => $currency->id,
            'priceable_type' => $variant->getMorphClass(),
            'priceable_id' => $variant->id,
        ]);

        return $variant;
    }

    /**
     * A cart that `createOrder()` accepts: addresses plus a shipping option.
     */
    public static function checkoutCart(Currency $currency): Cart
    {
        $cart = Cart::factory()->create(['currency_id' => $currency->id]);

        $shippingOption = new ShippingOption(
            name: 'Basic Delivery',
            description: 'Basic Delivery',
            identifier: 'BASDEL',
            price: new PriceValue(500, $currency, 1),
            taxClass: TaxClass::query()->where('default', true)->first(),
        );

        ShippingManifest::addOption($shippingOption);

        foreach (['billing', 'shipping'] as $type) {
            CartAddress::factory()->create([
                'cart_id' => $cart->id,
                'type' => $type,
                'country_id' => Country::factory(),
                'shipping_option' => $type === 'shipping' ? 'BASDEL' : null,
            ]);
        }

        return $cart->refresh();
    }
}
