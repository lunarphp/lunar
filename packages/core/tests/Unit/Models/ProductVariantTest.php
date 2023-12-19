<?php

namespace Lunar\Tests\Unit\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Lunar\Exceptions\MissingCurrencyPriceException;
use Lunar\Facades\Pricing;
use Lunar\Models\Currency;
use Lunar\Models\CustomerGroup;
use Lunar\Models\Price;
use Lunar\Models\Product;
use Lunar\Models\ProductVariant;
use Lunar\Models\TaxClass;
use Lunar\Models\TaxRate;
use Lunar\Models\TaxRateAmount;
use Lunar\Models\TaxZone;
use Lunar\Tests\TestCase;

/**
 * @group lunar.models
 */
class ProductVariantTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_create_prices_through_relationship()
    {
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
        ]);
        $currency = Currency::factory()->create([
            'decimal_places' => 2,
        ]);

        $variant->prices()->create([
            'price' => 199,
            'currency_id' => $currency->id,
        ]);

        $this->assertCount(1, $variant->prices);
    }

    /** @test */
    public function can_get_correct_price()
    {
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
        ]);
        $currency = Currency::factory()->create([
            'default' => true,
            'decimal_places' => 2,
        ]);

        $groupA = CustomerGroup::factory()->create([
            'handle' => 'default',
            'default' => true,
        ]);

        $groupB = CustomerGroup::factory()->create([
            'handle' => 'non_default',
            'default' => false,
        ]);

        $variant->prices()->createMany([
            [
                'price' => 100,
                'currency_id' => $currency->id,
                'tier' => 1,
            ],
            [
                'price' => 90,
                'currency_id' => $currency->id,
                'customer_group_id' => $groupA->id,
                'tier' => 1,
            ],
            [
                'price' => 80,
                'currency_id' => $currency->id,
                'customer_group_id' => $groupB->id,
                'tier' => 1,
            ],
            [
                'price' => 30,
                'currency_id' => $currency->id,
                'customer_group_id' => $groupB->id,
                'tier' => 5,
            ],
            [
                'price' => 60,
                'currency_id' => $currency->id,
                'tier' => 5,
            ],
        ]);

        $variant = $variant->load('prices');

        $this->assertEquals(Pricing::for($variant)->get()->matched->price->value, 90);
        $this->assertEquals(Pricing::qty(5)->for($variant)->get()->matched->price->value, 60);
        $this->assertEquals(Pricing::qty(5)->customerGroup($groupB)->for($variant)->get()->matched->price->value, 30);
        $this->assertEquals(Pricing::customerGroup($groupB)->for($variant)->get()->matched->price->value, 80);
        $this->assertEquals(Pricing::customerGroup($groupA)->for($variant)->get()->matched->price->value, 90);
    }

    /** @test */
    public function can_get_correct_price_based_on_currency()
    {
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->create([
            'product_id' => $product->id,
        ]);

        $currencyA = Currency::factory()->create([
            'decimal_places' => 2,
            'default' => true,
        ]);

        $currencyB = Currency::factory()->create([
            'decimal_places' => 2,
            'default' => false,
        ]);

        $currencyC = Currency::factory()->create([
            'decimal_places' => 2,
            'default' => false,
        ]);

        $variant->prices()->createMany([
            [
                'price' => 100,
                'currency_id' => $currencyA->id,
                'tier' => 1,
            ],
            [
                'price' => 200,
                'currency_id' => $currencyB->id,
                'tier' => 1,
            ],
        ]);

        $variant = $variant->load('prices');

        $this->assertEquals(Pricing::currency($currencyA)->for($variant)->get()->matched->price->value, 100);
        $this->assertEquals(Pricing::currency($currencyB)->for($variant)->get()->matched->price->value, 200);

        $this->expectException(MissingCurrencyPriceException::class);
        $this->assertEquals(Pricing::currency($currencyC)->for($variant)->get()->matched->price->value, 200);
    }

    /** @test */
    public function can_get_correct_price_inc_tax_based_on_tax_class()
    {
        Config::set('lunar.pricing.stored_inclusive_of_tax', false);

        $taxClassGeneric = TaxClass::factory()->create([
            'name' => 'Clothing',
        ]);
        $taxClassFood = TaxClass::factory()->create([
            'name' => 'Food',
        ]);

        $taxZoneIt = TaxZone::factory()->create([
            'name' => 'IT',
            'zone_type' => 'country',
            'price_display' => 'tax_inclusive',
            'active' => true,
            'default' => true,
        ]);

        $taxZoneFr = TaxZone::factory()->create([
            'name' => 'FR',
            'zone_type' => 'country',
            'price_display' => 'tax_inclusive',
            'active' => true,
            'default' => false,
        ]);

        $taxRateIt = TaxRate::factory()->create([
            'tax_zone_id' => $taxZoneIt->id,
            'name' => 'VAT',
        ]);

        $taxRateFr = TaxRate::factory()->create([
            'tax_zone_id' => $taxZoneFr->id,
            'name' => 'VAT',
        ]);

        TaxRateAmount::factory()->create([
            'tax_rate_id' => $taxRateIt->id,
            'tax_class_id' => $taxClassGeneric->id,
            'percentage' => 22,
        ]);
        TaxRateAmount::factory()->create([
            'tax_rate_id' => $taxRateFr->id,
            'tax_class_id' => $taxClassGeneric->id,
            'percentage' => 20,
        ]);
        TaxRateAmount::factory()->create([
            'tax_rate_id' => $taxRateIt->id,
            'tax_class_id' => $taxClassFood->id,
            'percentage' => 4,
        ]);

        $genericProduct = Product::factory()->create();
        $genericProductVariant = ProductVariant::factory()->create([
            'product_id' => $genericProduct->id,
            'tax_class_id' => $taxClassGeneric->id,
        ]);

        $foodProduct = Product::factory()->create();
        $foodProductVariant = ProductVariant::factory()->create([
            'product_id' => $foodProduct->id,
            'tax_class_id' => $taxClassFood->id,
        ]);

        $currency = Currency::factory()->create([
            'code' => 'EUR',
            'decimal_places' => 2,
            'default' => true,
        ]);

        Price::factory()->create([
            'price' => 10000,
            'currency_id' => $currency->id,
            'tier' => 1,
            'priceable_type' => ProductVariant::class,
            'priceable_id' => $genericProductVariant->id,
        ]);
        Price::factory()->create([
            'price' => 8000,
            'currency_id' => $currency->id,
            'tier' => 10,
            'priceable_type' => ProductVariant::class,
            'priceable_id' => $genericProductVariant->id,
        ]);
        Price::factory()->create([
            'price' => 400,
            'currency_id' => $currency->id,
            'tier' => 1,
            'priceable_type' => ProductVariant::class,
            'priceable_id' => $foodProductVariant->id,
        ]);

        $this->assertEquals(12200, $genericProductVariant->pricing()->currency($currency)->get()->matched->priceIncTax()->value);
        $this->assertEquals(416, $foodProductVariant->pricing()->currency($currency)->get()->matched->priceIncTax()->value);
        $this->assertEquals(9760, $genericProductVariant->pricing()->qty(20)->currency($currency)->get()->matched->priceIncTax()->value);
    }
}
