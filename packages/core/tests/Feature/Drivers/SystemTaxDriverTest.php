<?php

namespace Lunar\Tests\Feature\Drivers;

use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Lunar\Base\ValueObjects\Cart\TaxBreakdown;
use Lunar\DataTypes\Price;
use Lunar\Models\Price as PriceModel;
use Lunar\DataTypes\ShippingOption;
use Lunar\Drivers\SystemTaxDriver;
use Lunar\Facades\ShippingManifest;
use Lunar\Models\Address;
use Lunar\Models\Cart;
use Lunar\Models\CartLine;
use Lunar\Models\Country;
use Lunar\Models\Currency;
use Lunar\Models\ProductVariant;
use Lunar\Models\TaxClass;
use Lunar\Models\TaxRate;
use Lunar\Models\TaxRateAmount;
use Lunar\Models\TaxZone;
use Lunar\Models\TaxZoneCountry;
use Lunar\Tests\TestCase;

/**
 * @group lunar.taxdriver
 */
class SystemTaxDriverTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_set_shipping_address()
    {
        $address = Address::factory()->create();

        $driver = (new SystemTaxDriver)
            ->setShippingAddress($address);

        $this->assertInstanceOf(SystemTaxDriver::class, $driver);
    }

    /** @test */
    public function can_set_billing_address()
    {
        $address = Address::factory()->create();

        $driver = (new SystemTaxDriver)
            ->setBillingAddress($address);

        $this->assertInstanceOf(SystemTaxDriver::class, $driver);
    }

    /** @test */
    public function must_set_valid_address()
    {
        $this->expectException(\TypeError::class);

        $driver = (new SystemTaxDriver)
            ->setShippingAddress('ddd');

        $driver = (new SystemTaxDriver)
            ->setBillingAddress('ddd');
    }

    /** @test */
    public function can_set_currency()
    {
        $currency = Currency::factory()->create();

        $driver = (new SystemTaxDriver)
            ->setCurrency($currency);

        $this->assertInstanceOf(SystemTaxDriver::class, $driver);
    }

    /** @test */
    public function must_set_valid_currency()
    {
        $this->expectException(\TypeError::class);

        $driver = (new SystemTaxDriver)
            ->setCurrency('ddd');
    }

    /** @test */
    public function can_set_purchasable()
    {
        $variant = ProductVariant::factory()->create();

        $driver = (new SystemTaxDriver)
            ->setPurchasable($variant);

        $this->assertInstanceOf(SystemTaxDriver::class, $driver);
    }

    /** @test */
    public function can_set_cart_line()
    {
        CartLine::unsetEventDispatcher();

        $line = CartLine::factory()->create();

        $driver = (new SystemTaxDriver)
            ->setCartLine($line);

        $this->assertInstanceOf(SystemTaxDriver::class, $driver);
    }

    /** @test */
    public function can_get_breakdown()
    {
        $address = Address::factory()->create();
        $currency = Currency::factory()->create();
        $variant = ProductVariant::factory()->create();
        $line = CartLine::factory()->create();
        $subTotal = 833; // 8.33 in decimal

        $breakdown = (new SystemTaxDriver)
            ->setShippingAddress($address)
            ->setBillingAddress($address)
            ->setCurrency($currency)
            ->setPurchasable($variant)
            ->setCartLine($line)
            ->getBreakdown($subTotal);

        $this->assertInstanceOf(TaxBreakdown::class, $breakdown);
        $this->assertEquals(167, $breakdown->amounts[0]->price->value);
    }

    /** @test */
    public function can_get_breakdown_price_inc()
    {
        Config::set('lunar.pricing.stored_inclusive_of_tax', true);

        $address = Address::factory()->create();
        $currency = Currency::factory()->create();
        $line = CartLine::factory()->create();
        $subTotal = 999;

        $breakdown = (new SystemTaxDriver)
            ->setShippingAddress($address)
            ->setBillingAddress($address)
            ->setCurrency($currency)
            ->setPurchasable($line->purchasable)
            ->setCartLine($line)
            ->getBreakdown($subTotal);

        $this->assertInstanceOf(TaxBreakdown::class, $breakdown);
        $this->assertEquals(166, $breakdown->amounts[0]->price->value);
    }

    /** @test */
    public function can_get_breakdown_with_correct_tax_zone()
    {
        $address = Address::factory()->create();
        $currency = Currency::factory()->create();

        $defaultTaxZone = TaxZone::factory()->state(['default' => true])->create();
        $nonDefaultTaxZone1 = TaxZone::factory()->state(['default' => false])->create();
        $nonDefaultTaxZone2 = TaxZone::factory()->state(['default' => false])->create();

        $taxClass = TaxClass::factory()->has(
            TaxRateAmount::factory()
                ->count(4)
                ->state(new Sequence(
                    ['percentage' => 10, 'tax_rate_id' => TaxRate::factory()->state(['tax_zone_id' => $defaultTaxZone])],
                    ['percentage' => 15, 'tax_rate_id' => TaxRate::factory()->state(['tax_zone_id' => $defaultTaxZone])],
                    ['percentage' => 20, 'tax_rate_id' => TaxRate::factory()->state(['tax_zone_id' => $nonDefaultTaxZone1])],
                    ['percentage' => 25, 'tax_rate_id' => TaxRate::factory()->state(['tax_zone_id' => $nonDefaultTaxZone2])],
                ))
        )->create();

        $variant = ProductVariant::factory(['tax_class_id' => $taxClass->id])->create();
        $line = CartLine::factory(['purchasable_id' => $variant->id])->create();
        $subTotal = 1000; // 10.00 in decimal

        $breakdown = (new SystemTaxDriver)
            ->setShippingAddress($address)
            ->setBillingAddress($address)
            ->setCurrency($currency)
            ->setPurchasable($variant)
            ->setCartLine($line)
            ->getBreakdown($subTotal);

        $this->assertInstanceOf(TaxBreakdown::class, $breakdown);

        //Only the 2 tax rates from the default tax zone should have been applied
        $this->assertEquals(2, $breakdown->amounts->count());

        $this->assertEquals(100, $breakdown->amounts[0]->price->value);
        $this->assertEquals(150, $breakdown->amounts[1]->price->value);
    }

    /** @test */
    public function can_get_breakdown_with_tax_zone_with_tax_on_shipping()
    {
        Config::set('lunar.taxes.driver', 'system');

        $taxClass = TaxClass::factory()->create([
            'name' => 'Foobar',
        ]);

        $taxZoneWithTaxOnShipping = TaxZone::factory()->state([
            'name' => 'United Kingdom',
            'zone_type' => 'country',
            'default' => true,
            'tax_on_shipping' => true,
            'active' => true
        ])->create();

        $unitedKingdomCountry = Country::factory()->create([
            'name' => 'United Kingdom',
        ]);

        TaxZoneCountry::factory()->create([
            'country_id' => $unitedKingdomCountry->id,
            'tax_zone_id' => $taxZoneWithTaxOnShipping->id,
        ]);

        $taxRateClassWithTaxOnShipping = $taxZoneWithTaxOnShipping->taxRates()->firstOrCreate([
            'name' => 'VAT',
        ]);

        $taxRateClassWithTaxOnShipping->taxRateAmounts()->firstOrCreate([
            'percentage' => 20,
            'tax_class_id' => $taxClass->id,
        ]);

        $currency = Currency::factory()->create([
            'decimal_places' => 2,
        ]);

        $cart = Cart::factory()->create([
            'currency_id' => $currency->id,
        ]);

        $addressWithTaxOnShipping = Address::factory()->create([
            'country_id' => $unitedKingdomCountry->id,
        ]);

        $purchasable = ProductVariant::factory()->create([
            'tax_class_id' => $taxClass->id,
            'unit_quantity' => 1,
        ]);

        PriceModel::factory()->create([
            'price' => 2000,
            'tier' => 1,
            'currency_id' => $currency->id,
            'priceable_type' => get_class($purchasable),
            'priceable_id' => $purchasable->id,
        ]);

        $cart->lines()->create([
            'purchasable_type' => get_class($purchasable),
            'purchasable_id' => $purchasable->id,
            'quantity' => 1,
        ]);

        $cart->calculate();

        // Cart is without addresses
        $this->assertEquals(2000, $cart->subTotal->value);
        $this->assertEquals(400, $cart->taxTotal->value); // 20% tax on product
        $this->assertEquals(2400, $cart->total->value); // 2000 + 20% tax on product

        // Cart is with address with tax on shipping
        $cart->addAddress($addressWithTaxOnShipping, 'shipping');

        ShippingManifest::addOption(
            new ShippingOption(
                name: 'Basic Delivery',
                description: 'Basic Delivery',
                identifier: 'BASDEL',
                price: new Price(1000, $cart->currency, 1),
                taxClass: $taxClass
            )
        );

        $cart->shippingAddress->shipping_option = 'BASDEL';
        $cart->shippingAddress->save();

        $cart->calculate();
        $this->assertEquals(1000, $cart->shippingSubTotal->value);
        $this->assertEquals(1200, $cart->shippingTotal->value); // 1000 + 20% tax
        $this->assertEquals(600, $cart->taxTotal->value); // 20% tax on shipping + 20% tax on product
        $this->assertEquals(3600, $cart->total->value); // 2000 + 1000 + 20% tax on shipping + 20% tax on product

        Config::set('lunar.taxes.driver', 'test');
    }

    /** @test */
    public function can_get_breakdown_with_tax_without_tax_on_shipping()
    {
        Config::set('lunar.taxes.driver', 'system');

        $taxClass = TaxClass::factory()->create([
            'name' => 'Foobar',
        ]);

        $taxZoneWithoutTaxOnShipping = TaxZone::factory()->state([
            'name' => 'Belgium',
            'zone_type' => 'country',
            'default' => true,
            'tax_on_shipping' => false, // Default is false anyway
            'active' => true
        ])->create();

        $belgiumCountry = Country::factory()->create([
            'name' => 'Belgium',
        ]);

        TaxZoneCountry::factory()->create([
            'country_id' => $belgiumCountry->id,
            'tax_zone_id' => $taxZoneWithoutTaxOnShipping->id,
        ]);

        $taxRateClassWithoutTaxOnShipping = $taxZoneWithoutTaxOnShipping->taxRates()->firstOrCreate([
            'name' => 'VAT',
        ]);

        $taxRateClassWithoutTaxOnShipping->taxRateAmounts()->firstOrCreate([
            'percentage' => 20,
            'tax_class_id' => $taxClass->id,
        ]);

        $addressWithoutTaxOnShipping = Address::factory()->create([
            'country_id' => $belgiumCountry->id,
        ]);

        $currency = Currency::factory()->create([
            'decimal_places' => 2,
        ]);

        $cart = Cart::factory()->create([
            'currency_id' => $currency->id,
        ]);

        $purchasable = ProductVariant::factory()->create([
            'tax_class_id' => $taxClass->id,
            'unit_quantity' => 1,
        ]);

        PriceModel::factory()->create([
            'price' => 2000,
            'tier' => 1,
            'currency_id' => $currency->id,
            'priceable_type' => get_class($purchasable),
            'priceable_id' => $purchasable->id,
        ]);

        $cart->lines()->create([
            'purchasable_type' => get_class($purchasable),
            'purchasable_id' => $purchasable->id,
            'quantity' => 1,
        ]);

        $cart->calculate();

        // Cart is without addresses
        $this->assertEquals(2000, $cart->subTotal->value);
        $this->assertEquals(400, $cart->taxTotal->value); // 20% tax on product
        $this->assertEquals(2400, $cart->total->value); // 2000 + 20% tax on product

        // Cart is with address without tax on shipping
        $cart->addAddress($addressWithoutTaxOnShipping, 'shipping');

        ShippingManifest::addOption(
            new ShippingOption(
                name: 'Basic Delivery',
                description: 'Basic Delivery',
                identifier: 'BASDEL',
                price: new Price(1000, $cart->currency, 1),
                taxClass: $taxClass
            )
        );

        $cart->shippingAddress->shipping_option = 'BASDEL';
        $cart->shippingAddress->save();

        $cart->calculate();
        $this->assertEquals(1000, $cart->shippingSubTotal->value);
        $this->assertEquals(1000, $cart->shippingTotal->value); // 1000 + 0% tax
        $this->assertEquals(400, $cart->taxTotal->value); // 20% tax on product + 0% tax on shipping
        $this->assertEquals(3400, $cart->total->value); // 2000 + 1000 + 20% tax on product + 0% tax on shipping

        Config::set('lunar.taxes.driver', 'test');
    }
}
