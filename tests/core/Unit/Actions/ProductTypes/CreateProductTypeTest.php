<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Actions\ProductTypes\CreateProductType;
use Lunar\Core\Models\Attribute;
use Lunar\Core\Models\ProductType;
use Lunar\Core\States\ProductType\Active;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

test('creates a product type with the given attributes', function () {
    $productType = app(CreateProductType::class)->execute([
        'name' => 'Stationery',
        'handle' => 'stationery',
        'status' => 'draft',
        'description' => 'Pens, pencils and paper.',
    ]);

    expect($productType)->toBeInstanceOf(ProductType::class);

    $this->assertDatabaseHas('lunar_product_types', [
        'id' => $productType->id,
        'name' => 'Stationery',
        'handle' => 'stationery',
        'status' => 'draft',
        'description' => 'Pens, pencils and paper.',
    ]);
});

test('defaults to the active status', function () {
    $productType = app(CreateProductType::class)->execute([
        'name' => 'Stationery',
    ]);

    expect($productType->status)->toBeInstanceOf(Active::class);
});

test('generates a handle from the name when none is given', function () {
    $productType = app(CreateProductType::class)->execute([
        'name' => 'Stationery',
    ]);

    expect($productType->handle)->toBe('stationery');
});

test('syncs the given attribute mapping', function () {
    $attributes = Attribute::factory(2)->create();

    $productType = app(CreateProductType::class)->execute([
        'name' => 'Stationery',
    ], $attributes->pluck('id')->all());

    expect($productType->attributeMapping()->get())->toHaveCount(2);
});

test('does not touch the attribute mapping when none is given', function () {
    $productType = app(CreateProductType::class)->execute([
        'name' => 'Stationery',
    ]);

    expect($productType->attributeMapping()->get())->toHaveCount(0);
});
