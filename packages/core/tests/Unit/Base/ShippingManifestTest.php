<?php

namespace Lunar\Tests\Unit\Base;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\DataTypes\Price;
use Lunar\DataTypes\ShippingOption;
use Lunar\Facades\ShippingManifest;
use Lunar\Models\Cart;
use Lunar\Models\CartAddress;
use Lunar\Models\Country;
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

    private Cart $cart;

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
    public function can_add_multiple_options()
    {
        $this->assertCount(0, ShippingManifest::getOptions($this->cart));

        $taxClass = TaxClass::factory()->create();

        $options = collect();

        for ($i = 1; $i <= 5; $i++) {
            $options->push(new ShippingOption(
                name: 'Basic Delivery #'.$i,
                description: 'Basic Delivery',
                identifier: 'BASDEL'.$i,
                price: new Price(500, $this->cart->currency, 1),
                taxClass: $taxClass
            ));
        }

        ShippingManifest::addOptions($options);

        $manifestOptions = ShippingManifest::getOptions($this->cart);
        $this->assertCount(5, $manifestOptions);

        $this->assertSame($options->last(), $manifestOptions->last());
        $this->assertSame($options->first(), $manifestOptions->first());
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

    /** @test */
    public function can_retrieve_option()
    {
        $taxClass = TaxClass::factory()->create();

        ShippingManifest::addOption(
            $option1 = new ShippingOption(
                name: 'Basic Delivery',
                description: 'Basic Delivery',
                identifier: 'BASDEL',
                price: new Price(500, $this->cart->currency, 1),
                taxClass: $taxClass
            )
        );

        ShippingManifest::addOption(
            $option2 = new ShippingOption(
                name: 'Basic Delivery 2',
                description: 'Basic Delivery',
                identifier: 'BASDEL2',
                price: new Price(500, $this->cart->currency, 1),
                taxClass: $taxClass
            )
        );

        $this->assertCount(2, ShippingManifest::getOptions($this->cart));

        $this->assertSame($option1, ShippingManifest::getOption($this->cart, $option1->getIdentifier()));
    }

    /** @test */
    public function can_retrieve_cart_shipping_option()
    {
        $taxClass = TaxClass::factory()->create();

        ShippingManifest::addOption(
            $option = new ShippingOption(
                name: 'Basic Delivery',
                description: 'Basic Delivery',
                identifier: 'BASDEL',
                price: new Price(500, $this->cart->currency, 1),
                taxClass: $taxClass
            )
        );

        $shipping = CartAddress::factory()->make([
            'type' => 'shipping',
            'country_id' => Country::factory(),
            'first_name' => 'Santa',
            'line_one' => '123 Elf Road',
            'city' => 'Lapland',
            'postcode' => 'SHIPP',
        ]);

        $this->cart->setShippingAddress($shipping);

        $this->assertNull(ShippingManifest::getShippingOption($this->cart, $option->getIdentifier()));

        $this->cart->setShippingOption($option);

        $this->assertSame($option, ShippingManifest::getShippingOption($this->cart, $option->getIdentifier()));
    }

    /** @test */
    public function can_retrieve_cart_shipping_option_using()
    {
        $taxClass = TaxClass::factory()->create();

        $option = new ShippingOption(
            name: 'Basic Delivery',
            description: 'Basic Delivery',
            identifier: 'BASDEL',
            price: new Price(500, $this->cart->currency, 1),
            taxClass: $taxClass
        );

        $this->assertCount(0, ShippingManifest::getOptions($this->cart));

        $shipping = CartAddress::factory()->make([
            'type' => 'shipping',
            'country_id' => Country::factory(),
            'first_name' => 'Santa',
            'line_one' => '123 Elf Road',
            'city' => 'Lapland',
            'postcode' => 'SHIPP',
        ]);

        $this->cart->setShippingAddress($shipping);

        $this->cart->setShippingOption($option);

        $this->assertNull(ShippingManifest::getShippingOption($this->cart, $option->getIdentifier()));

        ShippingManifest::getOptionUsing(fn (Cart $cart, $identifier): ShippingOption => $option->getIdentifier() == $identifier ? $option : null);

        $this->assertSame($option, ShippingManifest::getShippingOption($this->cart, $option->getIdentifier()));
    }
}
