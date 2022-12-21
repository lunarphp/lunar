<?php

namespace Lunar\Tests\Unit\Base;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\DataTypes\Price;
use Lunar\DataTypes\ShippingOption;
use Lunar\Facades\ShippingManifest;
use Lunar\Models\Cart;
use Lunar\Models\Currency;
use Lunar\Models\Price as PriceModel;
use Lunar\Models\ProductVariant;
use Lunar\Models\TaxClass;
use Lunar\Models\TaxRateAmount;
use Lunar\Tests\TestCase;

/**
 * @group shipping-manifest
 */
class ShippingManifestTest extends TestCase
{
    use RefreshDatabase;

    private $cart;

    public function setUp(): void
    {
        parent::setUp();

        $currency = Currency::factory()->create([
            'decimal_places' => 2,
        ]);

        $this->cart = Cart::factory()->create([
            'currency_id' => $currency->id,
        ]);

        $taxClass = TaxClass::factory()->create([
            'name' => 'Foobar',
        ]);

        $taxClass->taxRateAmounts()->create(
            TaxRateAmount::factory()->make([
                'percentage' => 20,
                'tax_class_id' => $taxClass->id,
            ])->toArray()
        );

        $purchasable = ProductVariant::factory()->create([
            'tax_class_id' => $taxClass->id,
            'unit_quantity' => 1,
        ]);

        PriceModel::factory()->create([
            'price' => 100,
            'tier' => 1,
            'currency_id' => $currency->id,
            'priceable_type' => get_class($purchasable),
            'priceable_id' => $purchasable->id,
        ]);

        $this->cart->lines()->create([
            'purchasable_type' => get_class($purchasable),
            'purchasable_id' => $purchasable->id,
            'quantity' => 1,
        ]);
    }

    /** @test */
    public function can_add_option()
    {
        $this->assertCount(0, ShippingManifest::getOptions($this->cart));

        $taxClass = TaxClass::factory()->create();

        ShippingManifest::addOption(
            new ShippingOption(
                name: 'Basic Delivery',
                description: 'Basic Delivery',
                identifier: 'BASDEL',
                price: new Price(500, $this->cart->currency, 1),
                taxClass: $taxClass
            )
        );

        $this->assertCount(1, ShippingManifest::getOptions($this->cart));
    }

    /** @test */
    public function cannot_add_the_same_option_identifier_more_than_once()
    {
        $this->assertCount(0, ShippingManifest::getOptions($this->cart));

        $taxClass = TaxClass::factory()->create();

        ShippingManifest::addOption(
            new ShippingOption(
                name: 'Basic Delivery',
                description: 'Basic Delivery',
                identifier: 'BASDEL',
                price: new Price(500, $this->cart->currency, 1),
                taxClass: $taxClass
            )
        );

        ShippingManifest::addOption(
            new ShippingOption(
                name: 'Basic Delivery',
                description: 'Basic Delivery',
                identifier: 'BASDEL',
                price: new Price(500, $this->cart->currency, 1),
                taxClass: $taxClass
            )
        );

        $this->assertCount(1, ShippingManifest::getOptions($this->cart));
    }

    /** @test */
    public function can_clear_options()
    {
        $taxClass = TaxClass::factory()->create();

        ShippingManifest::addOption(
            new ShippingOption(
                name: 'Basic Delivery',
                description: 'Basic Delivery',
                identifier: 'BASDEL',
                price: new Price(500, $this->cart->currency, 1),
                taxClass: $taxClass
            )
        );

        $this->assertCount(1, ShippingManifest::getOptions($this->cart));

        ShippingManifest::clearOptions();

        $this->assertCount(0, ShippingManifest::getOptions($this->cart));
    }
}
