<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Exceptions\ProductTypeActionException;
use Lunar\Core\Models\Attribute;
use Lunar\Core\Models\AttributeGroup;
use Lunar\Core\Models\AttributeModel;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductType;
use Lunar\Core\Models\TaxClass;
use Lunar\Core\States\ProductType\Active;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

uses(RefreshDatabase::class);

test('can make a product type', function () {
    $productType = ProductType::factory()
        ->has(
            Attribute::factory()->for(AttributeGroup::factory(), 'group')->count(1),
            'attributeMapping',
        )
        ->create([
            'name' => 'Bob',
        ]);

    expect($productType->name)->toEqual('Bob');
});

test('defaults to the active state', function () {
    $productType = ProductType::create(['name' => 'Stationery', 'handle' => 'stationery']);

    expect($productType->status)->toBeInstanceOf(Active::class);
});

test('active scope filters out draft product types', function () {
    ProductType::factory()->active()->create();
    ProductType::factory()->draft()->create();

    expect(ProductType::active()->count())->toBe(1);
});

test('generates a handle from the name when none is given', function () {
    $productType = ProductType::create(['name' => 'Stationery']);

    expect($productType->handle)->toBe('stationery');
});

test('suffixes a generated handle until unique', function () {
    ProductType::factory()->create(['handle' => 'stationery']);
    ProductType::factory()->create(['handle' => 'stationery-2']);

    $productType = ProductType::create(['name' => 'Stationery']);

    expect($productType->handle)->toBe('stationery-3');
});

test('a replica gets a fresh handle', function () {
    $productType = ProductType::factory()->create(['name' => 'Stationery', 'handle' => 'stationery']);

    $replica = $productType->replicate();
    $replica->save();

    expect($replica->handle)->toBe('stationery-2');
});

test('can return its own mapped attributes', function () {
    Attribute::factory()
        ->has(AttributeModel::factory()->state(['model_type' => 'product_type']), 'models')
        ->create();

    $productType = ProductType::factory()->create();

    expect($productType->mappedAttributes)->toHaveCount(1);
});

test('the attribute mapping is distinct from the type\'s own attributes', function () {
    $ownAttribute = Attribute::factory()
        ->has(AttributeModel::factory()->state(['model_type' => 'product_type']), 'models')
        ->create();

    $mappedAttribute = Attribute::factory()
        ->has(AttributeModel::factory()->state(['model_type' => 'product']), 'models')
        ->create();

    $productType = ProductType::factory()->create();
    $productType->attributeMapping()->sync([$mappedAttribute->id]);

    expect($productType->mappedAttributes()->pluck('id')->all())->toBe([$ownAttribute->id])
        ->and($productType->attributeMapping()->allRelatedIds()->all())->toBe([$mappedAttribute->id])
        ->and($productType->productAttributes()->get()->modelKeys())->toBe([$mappedAttribute->id]);
});

test('can belong to a default tax class', function () {
    $taxClass = TaxClass::factory()->create();

    $productType = ProductType::factory()->create([
        'default_tax_class_id' => $taxClass->id,
    ]);

    expect($productType->defaultTaxClass->id)->toBe($taxClass->id);
});

test('refuses to delete a product type with products on any path', function () {
    $product = Product::factory()->create();
    $productType = $product->productType;

    expect(fn () => $productType->delete())
        ->toThrow(ProductTypeActionException::class);

    assertDatabaseHas(ProductType::class, ['id' => $productType->id]);

    // Reassigning the product unblocks deletion.
    $product->update(['product_type_id' => ProductType::factory()->create()->id]);
    $productType->refresh()->delete();

    assertDatabaseMissing(ProductType::class, ['id' => $productType->id]);
});
