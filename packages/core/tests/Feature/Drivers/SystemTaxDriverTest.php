<?php

namespace Lunar\Tests\Feature\Drivers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Lunar\Base\ValueObjects\Cart\TaxBreakdown;
use Lunar\Drivers\SystemTaxDriver;
use Lunar\Models\Address;
use Lunar\Models\CartLine;
use Lunar\Models\Currency;
use Lunar\Models\ProductVariant;
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
        $variant = ProductVariant::factory()->create();
        $line = CartLine::factory()->create();
        $subTotal = 999;

        $breakdown = (new SystemTaxDriver)
            ->setShippingAddress($address)
            ->setBillingAddress($address)
            ->setCurrency($currency)
            ->setPurchasable($variant)
            ->setCartLine($line)
            ->getBreakdown($subTotal);

        $this->assertInstanceOf(TaxBreakdown::class, $breakdown);
        $this->assertEquals(166, $breakdown->amounts[0]->price->value);
    }

}
