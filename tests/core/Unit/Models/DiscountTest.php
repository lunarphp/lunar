<?php

uses(\Lunar\Tests\Core\TestCase::class);

use Lunar\Models\Brand;
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
    $collectionC = Collection::factory()->create();

    // Discount with collection relationships using pivot table
    $discountWithCollectionA = Discount::factory()->create();
    $discountWithCollectionA->collections()->attach($collectionA->id, ['type' => 'limitation']);

    // Discount with multiple collections and different types
    $discountWithMultipleCollections = Discount::factory()->create();
    $discountWithMultipleCollections->collections()->attach($collectionA->id, ['type' => 'condition']);
    $discountWithMultipleCollections->collections()->attach($collectionB->id, ['type' => 'limitation']);

    // Discount with collection of different type
    $discountWithCollectionBReward = Discount::factory()->create();
    $discountWithCollectionBReward->collections()->attach($collectionB->id, ['type' => 'reward']);

    // Discount with no collections
    $discountWithoutCollections = Discount::factory()->create();

    // Test with specific collection IDs - should return discounts that either have NO collections OR have matching collections
    $discounts = Discount::query()->collections([$collectionA->id])->get();
    expect($discounts)->toHaveCount(3);
    expect($discounts->contains($discountWithCollectionA))->toBeTrue(); // Has matching collection
    expect($discounts->contains($discountWithMultipleCollections))->toBeTrue(); // Has matching collection
    expect($discounts->contains($discountWithoutCollections))->toBeTrue(); // No collections at all
    expect($discounts->contains($discountWithCollectionBReward))->toBeFalse(); // Has collections but not matching

    // Test with different collection ID
    $discounts = Discount::query()->collections([$collectionC->id])->get();
    expect($discounts)->toHaveCount(1);
    expect($discounts->contains($discountWithoutCollections))->toBeTrue();
    expect($discounts->contains($discountWithCollectionA))->toBeFalse();
    expect($discounts->contains($discountWithMultipleCollections))->toBeFalse();
    expect($discounts->contains($discountWithCollectionBReward))->toBeFalse();

    // Test with empty array (should return all discounts without any collection restrictions)
    $discounts = Discount::query()->collections([])->get();
    expect($discounts)->toHaveCount(1);
    expect($discounts->contains($discountWithoutCollections))->toBeTrue();
    expect($discounts->contains($discountWithCollectionA))->toBeFalse();
    expect($discounts->contains($discountWithMultipleCollections))->toBeFalse();
    expect($discounts->contains($discountWithCollectionBReward))->toBeFalse();

    // Test with types filter - limitation type
    $discounts = Discount::query()->collections([$collectionA->id], ['limitation'])->get();
    expect($discounts)->toHaveCount(3);
    expect($discounts->contains($discountWithCollectionA))->toBeTrue(); // Has limitation type for collectionA
    expect($discounts->contains($discountWithMultipleCollections))->toBeFalse(); // Has limitation type collections but not for collectionA
    expect($discounts->contains($discountWithoutCollections))->toBeTrue(); // No limitation type collections
    expect($discounts->contains($discountWithCollectionBReward))->toBeTrue(); // Doesn't have limitation type collections

    // Test with types filter - reward type
    $discounts = Discount::query()->collections([$collectionB->id], ['reward'])->get();
    expect($discounts)->toHaveCount(4);
    expect($discounts->contains($discountWithCollectionBReward))->toBeTrue(); // Has reward type for collectionB
    expect($discounts->contains($discountWithoutCollections))->toBeTrue(); // No reward type collections
    expect($discounts->contains($discountWithCollectionA))->toBeTrue(); // Doesn't have reward type collections
    expect($discounts->contains($discountWithMultipleCollections))->toBeTrue(); // Doesn't have reward type collections

    // Test with multiple types
    $discounts = Discount::query()->collections([$collectionA->id], ['limitation', 'condition'])->get();
    expect($discounts)->toHaveCount(4);
    expect($discounts->contains($discountWithCollectionA))->toBeTrue(); // Has limitation type for collectionA
    expect($discounts->contains($discountWithMultipleCollections))->toBeTrue(); // Has condition type for collectionA
    expect($discounts->contains($discountWithoutCollections))->toBeTrue(); // No limitation/condition type collections
    expect($discounts->contains($discountWithCollectionBReward))->toBeTrue(); // Doesn't have limitation/condition type collections

    // Test with string type instead of array
    $discounts = Discount::query()->collections([$collectionA->id], 'limitation')->get();
    expect($discounts)->toHaveCount(3);
    expect($discounts->contains($discountWithCollectionA))->toBeTrue(); // Has limitation type for collectionA
    expect($discounts->contains($discountWithMultipleCollections))->toBeFalse(); // Has limitation type collections but not for collectionA
    expect($discounts->contains($discountWithoutCollections))->toBeTrue(); // No limitation type collections
    expect($discounts->contains($discountWithCollectionBReward))->toBeTrue(); // Doesn't have limitation type collections
});

test('can apply brands scope', function () {
    $brandA = Brand::factory()->create();
    $brandB = Brand::factory()->create();
    $brandC = Brand::factory()->create();

    // Discount with brand relationships using pivot table
    $discountWithBrandA = Discount::factory()->create();
    $discountWithBrandA->brands()->attach($brandA->id, ['type' => 'limitation']);

    // Discount with multiple brands and different types
    $discountWithMultipleBrands = Discount::factory()->create();
    $discountWithMultipleBrands->brands()->attach($brandA->id, ['type' => 'condition']);
    $discountWithMultipleBrands->brands()->attach($brandB->id, ['type' => 'limitation']);

    // Discount with brand of different type
    $discountWithBrandBReward = Discount::factory()->create();
    $discountWithBrandBReward->brands()->attach($brandB->id, ['type' => 'reward']);

    // Discount with no brands
    $discountWithoutBrands = Discount::factory()->create();

    // Test with specific brand IDs - should return discounts that either have NO brands OR have matching brands
    $discounts = Discount::query()->brands([$brandA->id])->get();
    expect($discounts)->toHaveCount(3);
    expect($discounts->contains($discountWithBrandA))->toBeTrue(); // Has matching brand
    expect($discounts->contains($discountWithMultipleBrands))->toBeTrue(); // Has matching brand
    expect($discounts->contains($discountWithoutBrands))->toBeTrue(); // No brands at all
    expect($discounts->contains($discountWithBrandBReward))->toBeFalse(); // Has brands but not matching

    // Test with different brand ID
    $discounts = Discount::query()->brands([$brandC->id])->get();
    expect($discounts)->toHaveCount(1);
    expect($discounts->contains($discountWithoutBrands))->toBeTrue();
    expect($discounts->contains($discountWithBrandA))->toBeFalse();
    expect($discounts->contains($discountWithMultipleBrands))->toBeFalse();
    expect($discounts->contains($discountWithBrandBReward))->toBeFalse();

    // Test with empty array (should return all discounts without any brand restrictions)
    $discounts = Discount::query()->brands([])->get();
    expect($discounts)->toHaveCount(1);
    expect($discounts->contains($discountWithoutBrands))->toBeTrue();
    expect($discounts->contains($discountWithBrandA))->toBeFalse();
    expect($discounts->contains($discountWithMultipleBrands))->toBeFalse();
    expect($discounts->contains($discountWithBrandBReward))->toBeFalse();

    // Test with types filter - limitation type
    $discounts = Discount::query()->brands([$brandA->id], ['limitation'])->get();
    expect($discounts)->toHaveCount(3);
    expect($discounts->contains($discountWithBrandA))->toBeTrue(); // Has limitation type for brandA
    expect($discounts->contains($discountWithMultipleBrands))->toBeFalse(); // Has limitation type brands but not for brandA
    expect($discounts->contains($discountWithoutBrands))->toBeTrue(); // No limitation type brands
    expect($discounts->contains($discountWithBrandBReward))->toBeTrue(); // Doesn't have limitation type brands

    // Test with types filter - reward type
    $discounts = Discount::query()->brands([$brandB->id], ['reward'])->get();
    expect($discounts)->toHaveCount(4);
    expect($discounts->contains($discountWithBrandBReward))->toBeTrue(); // Has reward type for brandB
    expect($discounts->contains($discountWithoutBrands))->toBeTrue(); // No reward type brands
    expect($discounts->contains($discountWithBrandA))->toBeTrue(); // Doesn't have reward type brands
    expect($discounts->contains($discountWithMultipleBrands))->toBeTrue(); // Doesn't have reward type brands

    // Test with multiple types
    $discounts = Discount::query()->brands([$brandA->id], ['limitation', 'condition'])->get();
    expect($discounts)->toHaveCount(4);
    expect($discounts->contains($discountWithBrandA))->toBeTrue(); // Has limitation type for brandA
    expect($discounts->contains($discountWithMultipleBrands))->toBeTrue(); // Has condition type for brandA
    expect($discounts->contains($discountWithoutBrands))->toBeTrue(); // No limitation/condition type brands
    expect($discounts->contains($discountWithBrandBReward))->toBeTrue(); // Doesn't have limitation/condition type brands

    // Test with string type instead of array
    $discounts = Discount::query()->brands([$brandA->id], 'limitation')->get();
    expect($discounts)->toHaveCount(3);
    expect($discounts->contains($discountWithBrandA))->toBeTrue(); // Has limitation type for brandA
    expect($discounts->contains($discountWithMultipleBrands))->toBeFalse(); // Has limitation type brands but not for brandA
    expect($discounts->contains($discountWithoutBrands))->toBeTrue(); // No limitation type brands
    expect($discounts->contains($discountWithBrandBReward))->toBeTrue(); // Doesn't have limitation type brands
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
        'type' => 'limitation',
    ]);

    // Discount with collection discountables (different type)
    $discountWithCollections = Discount::factory()->create();
    $discountWithCollections->discountables()->create([
        'discountable_type' => Collection::morphName(),
        'discountable_id' => $collection->id,
        'type' => 'limitation',
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
        'type' => 'limitation',
    ]);

    // Discount with collection discountables (different type)
    $discountWithCollections = Discount::factory()->create();
    $discountWithCollections->discountables()->create([
        'discountable_type' => Collection::morphName(),
        'discountable_id' => $collection->id,
        'type' => 'limitation',
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
