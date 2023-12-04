<?php

uses(\Lunar\Tests\TestCase::class);
use Lunar\Actions\Collections\SortProductsBySku;
use Lunar\Models\Collection;
use Lunar\Models\Product;
use Lunar\Models\ProductVariant;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('can sort products with one variant each', function () {
    $products = Product::factory(2)->create();
    $collection = Collection::factory()->create([
        'sort' => 'min_price:asc',
    ]);

    $skus = [123, 234];

    foreach ($products as $index => $product) {
        ProductVariant::factory()->create([
            'product_id' => $product->id,
            'sku' => $skus[$index],
        ]);
    }

    $collection->products()->attach($products);

    expect($collection->products)->toHaveCount(2);

    $sortedProducts = app(SortProductsBySku::class)->execute(
        $collection->products,
        'asc'
    );

    expect($sortedProducts->first()->id)->toEqual($products->first()->id);
    expect($sortedProducts->last()->id)->toEqual($products->last()->id);

    $sortedProducts = app(SortProductsBySku::class)->execute(
        $collection->products,
        'desc'
    );

    expect($sortedProducts->last()->id)->toEqual($products->first()->id);
    expect($sortedProducts->first()->id)->toEqual($products->last()->id);
});

test('can sort products with multiple variants', function () {
    $products = Product::factory(2)->create();
    $collection = Collection::factory()->create([
        'sort' => 'min_price:asc',
    ]);

    $skus = [[1, 3], [2, 4]];

    foreach ($products as $index => $product) {
        ProductVariant::factory()->create([
            'product_id' => $product->id,
            'sku' => $skus[$index][0],
        ]);

        ProductVariant::factory()->create([
            'product_id' => $product->id,
            'sku' => $skus[$index][1],
        ]);
    }

    $collection->products()->attach($products);

    expect($collection->products)->toHaveCount(2);

    $sortedProducts = app(SortProductsBySku::class)->execute(
        $collection->products,
        'asc'
    );

    expect($sortedProducts->first()->id)->toEqual($products->first()->id);
    expect($sortedProducts->last()->id)->toEqual($products->last()->id);

    $sortedProducts = app(SortProductsBySku::class)->execute(
        $collection->products,
        'desc'
    );

    expect($sortedProducts->last()->id)->toEqual($products->first()->id);
    expect($sortedProducts->first()->id)->toEqual($products->last()->id);
});
