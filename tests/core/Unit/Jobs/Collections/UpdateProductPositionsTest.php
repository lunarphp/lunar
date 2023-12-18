<?php

uses(\Lunar\Tests\Core\TestCase::class);
use Lunar\Jobs\Collections\UpdateProductPositions;
use Lunar\Models\Collection;
use Lunar\Models\Currency;
use Lunar\Models\Price;
use Lunar\Models\Product;
use Lunar\Models\ProductVariant;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('can reorder products by price', function () {
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

    expect($collection->products)->toHaveCount(2);

    UpdateProductPositions::dispatch($collection->refresh());

    $collectionProducts = $collection->products()->get();

    expect($collectionProducts->first()->id)->toEqual($products->first()->id);
    expect($collectionProducts->last()->id)->toEqual($products->last()->id);

    // Set the sort direction to desc
    $collection->update([
        'sort' => 'min_price:desc',
    ]);

    UpdateProductPositions::dispatch($collection->refresh());

    $collectionProducts = $collection->products()->get();

    expect($collectionProducts->first()->id)->toEqual($products->last()->id);
    expect($collectionProducts->last()->id)->toEqual($products->first()->id);
});

test('can reorder products by sku', function () {
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

    expect($collection->products)->toHaveCount(2);

    UpdateProductPositions::dispatch($collection->refresh());

    $collectionProducts = $collection->products()->get();

    expect($collectionProducts->first()->id)->toEqual($products->first()->id);
    expect($collectionProducts->last()->id)->toEqual($products->last()->id);

    // Set the sort direction to desc
    $collection->update([
        'sort' => 'sku:desc',
    ]);

    UpdateProductPositions::dispatch($collection->refresh());

    $collectionProducts = $collection->products()->get();

    expect($collectionProducts->first()->id)->toEqual($products->last()->id);
    expect($collectionProducts->last()->id)->toEqual($products->first()->id);
});
