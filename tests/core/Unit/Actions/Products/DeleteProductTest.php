<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Actions\Products\DeleteProduct;
use Lunar\Core\Exceptions\ProductActionException;
use Lunar\Core\Models\OrderLine;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductVariant;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

test('deletes a product and its variants', function () {
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->create(['product_id' => $product->id]);

    app(DeleteProduct::class)->execute($product);

    $this->assertDatabaseMissing('lunar_products', ['id' => $product->id]);
    $this->assertDatabaseMissing('lunar_product_variants', ['id' => $variant->id]);
});

test('refuses to delete a product with order history', function () {
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->create(['product_id' => $product->id]);

    OrderLine::factory()->create([
        'purchasable_type' => $variant->getMorphClass(),
        'purchasable_id' => $variant->id,
    ]);

    expect(DeleteProduct::isProtected($product))->toBeTrue();

    expect(fn () => app(DeleteProduct::class)->execute($product))
        ->toThrow(ProductActionException::class);

    $this->assertDatabaseHas('lunar_products', ['id' => $product->id]);
});

test('the observer guards direct deletes too', function () {
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->create(['product_id' => $product->id]);

    OrderLine::factory()->create([
        'purchasable_type' => $variant->getMorphClass(),
        'purchasable_id' => $variant->id,
    ]);

    expect(fn () => $product->delete())->toThrow(ProductActionException::class);

    $this->assertDatabaseHas('lunar_products', ['id' => $product->id]);
    $this->assertDatabaseHas('lunar_product_variants', ['id' => $variant->id]);
});
