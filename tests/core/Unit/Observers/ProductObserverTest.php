<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Models\Product;
use Lunar\Models\ProductVariant;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class)->group('products.observer');

uses(RefreshDatabase::class);

test('soft deleting a product does not soft delete its variants', function () {
    $product = Product::factory()->create();

    $variants = ProductVariant::factory(2)->create([
        'product_id' => $product->id,
    ]);

    $product->delete();

    expect($product->fresh()->trashed())->toBeTrue();

    foreach ($variants as $variant) {
        expect($variant->fresh()->trashed())->toBeFalse();
    }
});

test('restoring a product does not restore variants the user trashed manually', function () {
    $product = Product::factory()->create();

    $keptVariant = ProductVariant::factory()->create([
        'product_id' => $product->id,
    ]);

    $manuallyTrashedVariant = ProductVariant::factory()->create([
        'product_id' => $product->id,
    ]);

    $manuallyTrashedVariant->delete();

    $product->delete();
    $product->restore();

    expect($keptVariant->fresh()->trashed())->toBeFalse()
        ->and($manuallyTrashedVariant->fresh()->trashed())->toBeTrue();
});

test('force deleting a product force deletes its variants, including trashed ones', function () {
    $product = Product::factory()->create();

    $activeVariant = ProductVariant::factory()->create([
        'product_id' => $product->id,
    ]);

    $trashedVariant = ProductVariant::factory()->create([
        'product_id' => $product->id,
    ]);

    $trashedVariant->delete();

    $product->forceDelete();

    expect(ProductVariant::withTrashed()->find($activeVariant->id))->toBeNull()
        ->and(ProductVariant::withTrashed()->find($trashedVariant->id))->toBeNull();
});
