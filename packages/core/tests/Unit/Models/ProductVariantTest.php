<?php

namespace GetCandy\Tests\Unit\Models;

use GetCandy\Exceptions\MissingCurrencyPriceException;
use GetCandy\Models\Currency;
use GetCandy\Models\CustomerGroup;
use GetCandy\Models\Product;
use GetCandy\Models\ProductVariant;
use GetCandy\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * @group getcandy.models
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
            'price'       => 199,
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
            'decimal_places' => 2,
        ]);

        $groupA = CustomerGroup::factory()->create([
            'handle'  => 'default',
            'default' => true,
        ]);

        $groupB = CustomerGroup::factory()->create([
            'handle'  => 'non_default',
            'default' => false,
        ]);

        $variant->prices()->createMany([
            [
                'price'       => 100,
                'currency_id' => $currency->id,
                'tier'        => 1,
            ],
            [
                'price'             => 90,
                'currency_id'       => $currency->id,
                'customer_group_id' => $groupA->id,
                'tier'              => 1,
            ],
            [
                'price'             => 80,
                'currency_id'       => $currency->id,
                'customer_group_id' => $groupB->id,
                'tier'              => 1,
            ],
            [
                'price'             => 30,
                'currency_id'       => $currency->id,
                'customer_group_id' => $groupB->id,
                'tier'              => 5,
            ],
            [
                'price'       => 60,
                'currency_id' => $currency->id,
                'tier'        => 5,
            ],
        ]);

        $variant = $variant->load('prices');

        $this->assertEquals(100, $variant->getPrice(1, $currency));
        $this->assertEquals(60, $variant->getPrice(5, $currency));
        $this->assertEquals(30, $variant->getPrice(5, $currency, collect([$groupB])));
        $this->assertEquals(80, $variant->getPrice(1, $currency, collect([$groupB])));
        $this->assertEquals(90, $variant->getPrice(1, $currency, collect([$groupA])));
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
            'default'        => true,
        ]);

        $currencyB = Currency::factory()->create([
            'decimal_places' => 2,
            'default'        => false,
        ]);

        $currencyC = Currency::factory()->create([
            'decimal_places' => 2,
            'default'        => false,
        ]);

        $variant->prices()->createMany([
            [
                'price'       => 100,
                'currency_id' => $currencyA->id,
                'tier'        => 1,
            ],
            [
                'price'       => 200,
                'currency_id' => $currencyB->id,
                'tier'        => 1,
            ],
        ]);

        $variant = $variant->load('prices');

        $this->assertEquals(100, $variant->getPrice(1, $currencyA));
        $this->assertEquals(200, $variant->getPrice(1, $currencyB));

        $this->expectException(MissingCurrencyPriceException::class);
        $variant->getPrice(1, $currencyC);
    }
}
