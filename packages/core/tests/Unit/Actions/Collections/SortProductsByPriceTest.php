<?php

namespace GetCandy\Tests\Unit\Actions\Collections;

use GetCandy\Actions\Collections\SortProductsByPrice;
use GetCandy\Models\Collection;
use GetCandy\Models\Currency;
use GetCandy\Models\Price;
use GetCandy\Models\Product;
use GetCandy\Models\ProductVariant;
use GetCandy\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * @group getcandy.actions
 */
class SortProductsByPriceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_sort_products()
    {
        Currency::factory()->create([
            'default' => true,
        ]);

        $products = Product::factory(2)->create();
        $collection = Collection::factory()->create([
            'sort' => 'min_price:asc',
        ]);

        $prices = [199, 299];

        foreach ($products as $index => $product) {
            $variant = ProductVariant::factory()->create([
                'product_id' => $product->id,
            ]);

            foreach (Currency::get() as $currency) {
                Price::factory()->create([
                    'priceable_type' => ProductVariant::class,
                    'priceable_id'   => $variant->id,
                    'currency_id'    => $currency->id,
                    'tier'           => 1,
                    'price'          => $prices[$index],
                ]);
            }
        }

        $collection->products()->attach($products);

        $this->assertCount(2, $collection->products);

        $sortedProducts = app(SortProductsByPrice::class)->execute(
            $collection->products,
            $currency,
            'asc',
        );

        $this->assertEquals($products->first()->id, $sortedProducts->first()->id);
        $this->assertEquals($products->last()->id, $sortedProducts->last()->id);

        // Set the sort direction to desc
        $sortedProducts = app(SortProductsByPrice::class)->execute(
            $collection->products,
            $currency,
            'desc',
        );

        $this->assertEquals($products->last()->id, $sortedProducts->first()->id);
        $this->assertEquals($products->first()->id, $sortedProducts->last()->id);
    }
}
