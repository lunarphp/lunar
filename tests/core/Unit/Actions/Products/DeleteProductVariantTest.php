<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Actions\Products\DeleteProductVariant;
use Lunar\Core\Exceptions\ProductActionException;
use Lunar\Core\Models\OrderLine;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductVariant;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

test('deletes a variant with siblings', function () {
    $product = Product::factory()->create();
    [$variant] = ProductVariant::factory()->count(2)->create(['product_id' => $product->id]);

    app(DeleteProductVariant::class)->execute($variant);

    $this->assertDatabaseMissing('lunar_product_variants', ['id' => $variant->id]);
    expect($product->variants()->count())->toBe(1);
});

test('refuses to delete a variant with order history', function () {
    $product = Product::factory()->create();
    [$variant] = ProductVariant::factory()->count(2)->create(['product_id' => $product->id]);

    OrderLine::factory()->create([
        'purchasable_type' => $variant->getMorphClass(),
        'purchasable_id' => $variant->id,
    ]);

    expect(fn () => app(DeleteProductVariant::class)->execute($variant))
        ->toThrow(ProductActionException::class);

    $this->assertDatabaseHas('lunar_product_variants', ['id' => $variant->id]);
});

test('refuses to delete the last variant', function () {
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->create(['product_id' => $product->id]);

    expect(fn () => app(DeleteProductVariant::class)->execute($variant))
        ->toThrow(ProductActionException::class);

    $this->assertDatabaseHas('lunar_product_variants', ['id' => $variant->id]);
});

test('the observer guards direct deletes of ordered variants', function () {
    $variant = ProductVariant::factory()->create();

    OrderLine::factory()->create([
        'purchasable_type' => $variant->getMorphClass(),
        'purchasable_id' => $variant->id,
    ]);

    expect(fn () => $variant->delete())->toThrow(ProductActionException::class);
});

test('deleting a whole product still cascades through its final variant', function () {
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->create(['product_id' => $product->id]);

    $product->delete();

    $this->assertDatabaseMissing('lunar_product_variants', ['id' => $variant->id]);
});
