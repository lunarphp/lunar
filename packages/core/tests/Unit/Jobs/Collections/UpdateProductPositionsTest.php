<?php

namespace GetCandy\Tests\Unit\Jobs\Collections;

use GetCandy\Jobs\Collections\UpdateProductPositions;
use GetCandy\Models\Collection;
use GetCandy\Models\Currency;
use GetCandy\Models\Price;
use GetCandy\Models\Product;
use GetCandy\Models\ProductVariant;
use GetCandy\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * @group getcandy.jobs
 */
class NumberTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_reorder_products_by_price()
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
                    'priceable_id' => $variant->id,
                    'currency_id' => $currency->id,
                    'tier' => 1,
                    'price' => $prices[$index],
                ]);
            }
        }

        $collection->products()->attach($products);

        $this->assertCount(2, $collection->products);

        UpdateProductPositions::dispatch($collection->refresh());

        $collectionProducts = $collection->products()->get();

        $this->assertEquals($products->first()->id, $collectionProducts->first()->id);
        $this->assertEquals($products->last()->id, $collectionProducts->last()->id);

        // Set the sort direction to desc
        $collection->update([
            'sort' => 'min_price:desc',
        ]);

        UpdateProductPositions::dispatch($collection->refresh());

        $collectionProducts = $collection->products()->get();

        $this->assertEquals($products->last()->id, $collectionProducts->first()->id);
        $this->assertEquals($products->first()->id, $collectionProducts->last()->id);
    }

    /** @test */
    public function can_reorder_products_by_sku()
    {
        $products = Product::factory(2)->create();
        $collection = Collection::factory()->create([
            'sort' => 'sku:asc',
        ]);

        $skus = [123, 234];

        foreach ($products as $index => $product) {
            ProductVariant::factory()->create([
                'product_id' => $product->id,
                'sku' => $skus[$index],
            ]);
        }

        $collection->products()->attach($products);

        $this->assertCount(2, $collection->products);

        UpdateProductPositions::dispatch($collection->refresh());

        $collectionProducts = $collection->products()->get();

        $this->assertEquals($products->first()->id, $collectionProducts->first()->id);
        $this->assertEquals($products->last()->id, $collectionProducts->last()->id);

        // Set the sort direction to desc
        $collection->update([
            'sort' => 'sku:desc',
        ]);

        UpdateProductPositions::dispatch($collection->refresh());

        $collectionProducts = $collection->products()->get();

        $this->assertEquals($products->last()->id, $collectionProducts->first()->id);
        $this->assertEquals($products->first()->id, $collectionProducts->last()->id);
    }
}
