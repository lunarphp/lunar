<?php

namespace Lunar\Tests\Feature\Drivers;

use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Lunar\Base\ValueObjects\Cart\TaxBreakdown;
use Lunar\Drivers\SystemTaxDriver;
use Lunar\Models\Address;
use Lunar\Models\CartLine;
use Lunar\Models\Currency;
use Lunar\Models\ProductVariant;
use Lunar\Models\TaxClass;
use Lunar\Models\TaxRate;
use Lunar\Models\TaxRateAmount;
use Lunar\Models\TaxZone;
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
}
