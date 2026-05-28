<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductVariant;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class)->group('products.observer');

uses(RefreshDatabase::class);

test('deleting a product deletes its variants', function () {
    $product = Product::factory()->create();

    $variants = ProductVariant::factory(2)->create([
        'product_id' => $product->id,
    ]);

    $product->delete();

    expect(Product::find($product->id))->toBeNull();

    foreach ($variants as $variant) {
        expect(ProductVariant::find($variant->id))->toBeNull();
    }
});
