<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection as SupportCollection;
use Lunar\Core\Models\Brand;
use Lunar\Core\Models\Collection;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductVariant;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

test('product name, description and short_description are translatable columns', function () {
    $product = Product::factory()->create([
        'name' => collect(['en' => 'Trainers', 'fr' => 'Baskets']),
        'description' => collect(['en' => 'Comfy shoes']),
        'short_description' => collect(['en' => 'Comfy']),
    ]);

    $product->refresh();

    expect($product->name)->toBeInstanceOf(SupportCollection::class);
    expect($product->translate('name'))->toBe('Trainers');
    expect($product->translate('name', 'fr'))->toBe('Baskets');
    expect($product->translate('description'))->toBe('Comfy shoes');
    expect($product->translate('short_description'))->toBe('Comfy');

    // recordTitle resolves through the column.
    expect($product->recordTitle)->toBe('Trainers');
});

test('the product factory seeds the dedicated translatable columns', function () {
    $product = Product::factory()->create();

    expect($product->translate('name'))->not->toBeNull();
    expect($product->getAttribute('name'))->toBeInstanceOf(SupportCollection::class);
    expect($product->attribute_data)->toBeInstanceOf(SupportCollection::class);
});

test('a product variant describes itself using the parent product name column', function () {
    $product = Product::factory()->create([
        'name' => collect(['en' => 'Trainers']),
    ]);

    $variant = ProductVariant::factory()->create(['product_id' => $product->id]);

    expect($variant->getDescription())->toBe('Trainers');
});

test('collection breadcrumb maps ancestor names through the column', function () {
    $parent = Collection::factory()->create([
        'name' => collect(['en' => 'Footwear']),
    ]);

    $child = Collection::factory()->create([
        'collection_group_id' => $parent->collection_group_id,
        'name' => collect(['en' => 'Trainers']),
    ]);

    $parent->appendNode($child);

    expect($child->refresh()->breadcrumb->all())->toBe(['Footwear']);
});

test('brand keeps a plain string name but gains translatable description columns', function () {
    $brand = Brand::factory()->create([
        'name' => 'Nike',
        'description' => collect(['en' => 'Just do it', 'fr' => 'Vas-y']),
    ]);

    $brand->refresh();

    expect($brand->name)->toBe('Nike');
    expect($brand->translate('description'))->toBe('Just do it');
    expect($brand->translate('description', 'fr'))->toBe('Vas-y');
});
