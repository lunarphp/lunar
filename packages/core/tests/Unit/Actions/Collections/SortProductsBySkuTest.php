<?php

namespace Lunar\Tests\Unit\Actions\Collections;

use Lunar\Actions\Collections\SortProductsBySku;
use Lunar\Models\Collection;
use Lunar\Models\Product;
use Lunar\Models\ProductVariant;
use Lunar\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * @group getcandy.actions
 */
class SortProductsBySkuTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_sort_products_with_one_variant_each()
    {
        $products = Product::factory(2)->create();
        $collection = Collection::factory()->create([
            'sort' => 'min_price:asc',
        ]);

        $skus = [123, 234];

        foreach ($products as $index => $product) {
            ProductVariant::factory()->create([
                'product_id' => $product->id,
                'sku'        => $skus[$index],
            ]);
        }

        $collection->products()->attach($products);

        $this->assertCount(2, $collection->products);

        $sortedProducts = app(SortProductsBySku::class)->execute(
            $collection->products,
            'asc'
        );

        $this->assertEquals($products->first()->id, $sortedProducts->first()->id);
        $this->assertEquals($products->last()->id, $sortedProducts->last()->id);

        $sortedProducts = app(SortProductsBySku::class)->execute(
            $collection->products,
            'desc'
        );

        $this->assertEquals($products->first()->id, $sortedProducts->last()->id);
        $this->assertEquals($products->last()->id, $sortedProducts->first()->id);
    }

    /** @test */
    public function can_sort_products_with_multiple_variants()
    {
        $products = Product::factory(2)->create();
        $collection = Collection::factory()->create([
            'sort' => 'min_price:asc',
        ]);

        $skus = [[1, 3], [2, 4]];

        foreach ($products as $index => $product) {
            ProductVariant::factory()->create([
                'product_id' => $product->id,
                'sku'        => $skus[$index][0],
            ]);

            ProductVariant::factory()->create([
                'product_id' => $product->id,
                'sku'        => $skus[$index][1],
            ]);
        }

        $collection->products()->attach($products);

        $this->assertCount(2, $collection->products);

        $sortedProducts = app(SortProductsBySku::class)->execute(
            $collection->products,
            'asc'
        );

        $this->assertEquals($products->first()->id, $sortedProducts->first()->id);
        $this->assertEquals($products->last()->id, $sortedProducts->last()->id);

        $sortedProducts = app(SortProductsBySku::class)->execute(
            $collection->products,
            'desc'
        );

        $this->assertEquals($products->first()->id, $sortedProducts->last()->id);
        $this->assertEquals($products->last()->id, $sortedProducts->first()->id);
    }
}
