<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Actions\ProductTypes\UpdateProductType;
use Lunar\Core\Models\Attribute;
use Lunar\Core\Models\ProductType;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

test('updates a product type\'s attributes', function () {
    $productType = ProductType::factory()->create(['name' => 'Stationery']);

    app(UpdateProductType::class)->execute($productType, [
        'name' => 'Office Supplies',
        'status' => 'draft',
        'description' => 'Everything for the desk.',
    ]);

    $this->assertDatabaseHas('lunar_product_types', [
        'id' => $productType->id,
        'name' => 'Office Supplies',
        'status' => 'draft',
        'description' => 'Everything for the desk.',
    ]);
});

test('syncs the given attribute mapping', function () {
    $productType = ProductType::factory()->create();
    [$attributeA, $attributeB] = Attribute::factory(2)->create();

    $productType->attributeMapping()->sync([$attributeA->id]);

    app(UpdateProductType::class)->execute($productType, [], [$attributeB->id]);

    expect($productType->attributeMapping()->allRelatedIds()->all())->toBe([$attributeB->id]);
});

test('an empty attribute set clears the mapping', function () {
    $productType = ProductType::factory()->create();
    $attribute = Attribute::factory()->create();

    $productType->attributeMapping()->sync([$attribute->id]);

    app(UpdateProductType::class)->execute($productType, [], []);

    expect($productType->attributeMapping()->get())->toHaveCount(0);
});

test('null leaves the mapping untouched', function () {
    $productType = ProductType::factory()->create();
    $attribute = Attribute::factory()->create();

    $productType->attributeMapping()->sync([$attribute->id]);

    app(UpdateProductType::class)->execute($productType, ['name' => 'Renamed']);

    expect($productType->attributeMapping()->allRelatedIds()->all())->toBe([$attribute->id]);
});
