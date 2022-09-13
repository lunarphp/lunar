<?php

namespace Lunar\Tests\Unit\Models;

use Lunar\Exceptions\MissingCurrencyPriceException;
use Lunar\Facades\Pricing;
use Lunar\Models\Currency;
use Lunar\Models\CustomerGroup;
use Lunar\Models\Product;
use Lunar\Models\ProductVariant;
use Lunar\Tests\TestCase;
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
            'default'        => true,
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

        $this->assertEquals(Pricing::for($variant)->get()->matched->price->value, 100);
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

        $this->assertEquals(Pricing::currency($currencyA)->for($variant)->get()->matched->price->value, 100);
        $this->assertEquals(Pricing::currency($currencyB)->for($variant)->get()->matched->price->value, 200);

        $this->expectException(MissingCurrencyPriceException::class);
        $this->assertEquals(Pricing::currency($currencyC)->for($variant)->get()->matched->price->value, 200);
    }
}
