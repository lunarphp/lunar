<?php

uses(\Lunar\Tests\Core\TestCase::class);

use Lunar\Actions\Collections\SortProductsByPrice;
use Lunar\Models\Collection;
use Lunar\Models\Currency;
use Lunar\Models\Price;
use Lunar\Models\Product;
use Lunar\Models\ProductVariant;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('can sort products', function () {
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
                'priceable_type' => $variant->getMorphClass(),
                'priceable_id' => $variant->id,
                'currency_id' => $currency->id,
                'min_quantity' => 1,
                'price' => $prices[$index],
            ]);
        }
    }

    $collection->products()->attach($products);

    expect($collection->products)->toHaveCount(2);

    $sortedProducts = app(SortProductsByPrice::class)->execute(
        $collection->products,
        $currency,
        'asc',
    );

    expect($sortedProducts->first()->id)->toEqual($products->first()->id);
    expect($sortedProducts->last()->id)->toEqual($products->last()->id);

    // Set the sort direction to desc
    $sortedProducts = app(SortProductsByPrice::class)->execute(
        $collection->products,
        $currency,
        'desc',
    );

    expect($sortedProducts->first()->id)->toEqual($products->last()->id);
    expect($sortedProducts->last()->id)->toEqual($products->first()->id);
});
