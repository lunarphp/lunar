<?php

uses(\Lunar\Tests\Core\TestCase::class);

use Lunar\Models\Collection;
use Lunar\Models\Discount;
use Lunar\Models\Product;
use Lunar\Models\ProductVariant;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('can apply usable scope', function () {
    Discount::factory()->create([
        'max_uses' => null,
    ]);

    Discount::factory()->create([
        'uses' => 10,
        'max_uses' => 11,
    ]);

    $discountC = Discount::factory()->create([
        'uses' => 10,
        'max_uses' => 10,
    ]);

    $discounts = Discount::usable()->get();

    expect($discounts)->toHaveCount(2);
    expect($discounts->first(
        fn ($discount) => $discount->id == $discountC->id
    ))->toBeNull();
});

test('can apply collections scope', function () {
    $collectionA = Collection::factory()->create();
    $collectionB = Collection::factory()->create();
    $product = Product::factory()->create();

    // Discount with collection discountables
    $discountWithCollections = Discount::factory()->create();
    $discountWithCollections->discountables()->create([
        'discountable_type' => Collection::morphName(),
        'discountable_id' => $collectionA->id,
        'type' => 'condition',
    ]);

    // Discount with product discountables (different type)
    $discountWithProducts = Discount::factory()->create();
    $discountWithProducts->discountables()->create([
        'discountable_type' => Product::morphName(),
        'discountable_id' => $product->id,
        'type' => 'condition',
    ]);

    // Discount with no discountables
    $discountWithoutDiscountables = Discount::factory()->create();

    // Test with specific collection IDs - should return discounts that either have matching collections OR no collection restrictions
    $discounts = Discount::query()->collections([$collectionA->id])->get();
    expect($discounts)->toHaveCount(3);
    expect($discounts->contains($discountWithCollections))->toBeTrue(); // Matches collection
    expect($discounts->contains($discountWithProducts))->toBeTrue(); // No collection restrictions
    expect($discounts->contains($discountWithoutDiscountables))->toBeTrue(); // No collection restrictions

    // Test with different collection ID
    $discounts = Discount::query()->collections([$collectionB->id])->get();
    expect($discounts)->toHaveCount(2);
    expect($discounts->contains($discountWithProducts))->toBeTrue();
    expect($discounts->contains($discountWithoutDiscountables))->toBeTrue();
    expect($discounts->contains($discountWithCollections))->toBeFalse(); // Doesn't match collection

    // Test with empty array (should return all discounts without collection discountables)
    $discounts = Discount::query()->collections([])->get();
    expect($discounts)->toHaveCount(2);
    expect($discounts->contains($discountWithoutDiscountables))->toBeTrue();
    expect($discounts->contains($discountWithProducts))->toBeTrue();
    expect($discounts->contains($discountWithCollections))->toBeFalse();
});

test('can apply products scope', function () {
    $productA = Product::factory()->create();
    $productB = Product::factory()->create();
    $collection = Collection::factory()->create();

    // Discount with product discountables
    $discountWithProducts = Discount::factory()->create();
    $discountWithProducts->discountables()->create([
        'discountable_type' => Product::morphName(),
        'discountable_id' => $productA->id,
        'type' => 'condition',
    ]);

    // Discount with collection discountables (different type)
    $discountWithCollections = Discount::factory()->create();
    $discountWithCollections->discountables()->create([
        'discountable_type' => Collection::morphName(),
        'discountable_id' => $collection->id,
        'type' => 'condition',
    ]);

    // Discount with no discountables
    $discountWithoutDiscountables = Discount::factory()->create();

    // Test with specific product IDs
    $discounts = Discount::query()->products([$productA->id])->get();
    expect($discounts)->toHaveCount(3);
    expect($discounts->contains($discountWithProducts))->toBeTrue(); // Matches product
    expect($discounts->contains($discountWithCollections))->toBeTrue(); // No product restrictions
    expect($discounts->contains($discountWithoutDiscountables))->toBeTrue(); // No product restrictions

    // Test with different product ID
    $discounts = Discount::query()->products([$productB->id])->get();
    expect($discounts)->toHaveCount(2);
    expect($discounts->contains($discountWithCollections))->toBeTrue();
    expect($discounts->contains($discountWithoutDiscountables))->toBeTrue();
    expect($discounts->contains($discountWithProducts))->toBeFalse(); // Doesn't match product

    // Test with empty array
    $discounts = Discount::query()->products([])->get();
    expect($discounts)->toHaveCount(2);
    expect($discounts->contains($discountWithoutDiscountables))->toBeTrue();
    expect($discounts->contains($discountWithCollections))->toBeTrue();
    expect($discounts->contains($discountWithProducts))->toBeFalse();
});

test('can apply product variants scope', function () {
    $product = Product::factory()->create();
    $variantA = ProductVariant::factory()->create(['product_id' => $product->id]);
    $variantB = ProductVariant::factory()->create(['product_id' => $product->id]);
    $collection = Collection::factory()->create();

    // Discount with variant discountables
    $discountWithVariants = Discount::factory()->create();
    $discountWithVariants->discountables()->create([
        'discountable_type' => ProductVariant::morphName(),
        'discountable_id' => $variantA->id,
        'type' => 'condition',
    ]);

    // Discount with collection discountables (different type)
    $discountWithCollections = Discount::factory()->create();
    $discountWithCollections->discountables()->create([
        'discountable_type' => Collection::morphName(),
        'discountable_id' => $collection->id,
        'type' => 'condition',
    ]);

    // Discount with no discountables
    $discountWithoutDiscountables = Discount::factory()->create();

    // Test with specific variant IDs
    $discounts = Discount::query()->productVariants([$variantA->id])->get();
    expect($discounts)->toHaveCount(3);
    expect($discounts->contains($discountWithVariants))->toBeTrue(); // Matches variant
    expect($discounts->contains($discountWithCollections))->toBeTrue(); // No variant restrictions
    expect($discounts->contains($discountWithoutDiscountables))->toBeTrue(); // No variant restrictions

    // Test with different variant ID
    $discounts = Discount::query()->productVariants([$variantB->id])->get();
    expect($discounts)->toHaveCount(2);
    expect($discounts->contains($discountWithCollections))->toBeTrue();
    expect($discounts->contains($discountWithoutDiscountables))->toBeTrue();
    expect($discounts->contains($discountWithVariants))->toBeFalse(); // Doesn't match variant

    // Test with empty array
    $discounts = Discount::query()->productVariants([])->get();
    expect($discounts)->toHaveCount(2);
    expect($discounts->contains($discountWithoutDiscountables))->toBeTrue();
    expect($discounts->contains($discountWithCollections))->toBeTrue();
    expect($discounts->contains($discountWithVariants))->toBeFalse();
});
