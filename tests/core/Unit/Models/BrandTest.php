<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Lunar\Generators\UrlGenerator;
use Lunar\Models\Attribute;
use Lunar\Models\Brand;
use Lunar\Models\Collection;
use Lunar\Models\Discount;
use Lunar\Models\Language;
use Lunar\Models\Product;
use Lunar\Models\Url;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

uses(RefreshDatabase::class);

test('can make a brand', function () {
    $brand = Brand::factory()->create([
        'name' => 'Test Brand',
    ]);
    expect($brand->name)->toEqual('Test Brand');
});

test('can generate url', function () {
    Config::set('lunar.urls.generator', UrlGenerator::class);

    Language::factory()->create([
        'default' => true,
    ]);

    $brand = Brand::factory()->create([
        'name' => 'Test Brand',
    ]);

    $this->assertDatabaseHas((new Url)->getTable(), [
        'slug' => 'test-brand',
        'element_type' => $brand->getMorphClass(),
        'element_id' => $brand->id,
    ]);
});

test('generates unique urls', function () {
    Config::set('lunar.urls.generator', UrlGenerator::class);

    Language::factory()->create([
        'default' => true,
    ]);

    $brand1 = Brand::factory()->create([
        'name' => 'Test Brand',
    ]);

    $brand2 = Brand::factory()->create([
        'name' => 'Test Brand',
    ]);

    $brand3 = Brand::factory()->create([
        'name' => 'Test Brand',
    ]);

    $brand4 = Brand::factory()->create([
        'name' => 'Brand Test',
    ]);

    expect($brand1->urls->first()->slug)->toEqual('test-brand');

    expect($brand2->urls->first()->slug)->toEqual('test-brand-2');

    expect($brand3->urls->first()->slug)->toEqual('test-brand-3');

    expect($brand4->urls->first()->slug)->toEqual('brand-test');
});

test('can return mapped attributes', function () {
    Attribute::factory()->create([
        'attribute_type' => 'brand',
    ]);
    $brand = Brand::factory()->create([
        'name' => 'Test Brand',
    ]);
    expect($brand->mappedAttributes)->toHaveCount(1);
});

test('can delete a brand', function () {
    $brand = Brand::factory()->create([
        'name' => 'Test Brand',
    ]);

    $activeProduct = Product::factory()->create([
        'brand_id' => $brand->id,
    ]);

    $trashedProduct = Product::factory()->create([
        'brand_id' => $brand->id,
    ]);
    $trashedProduct->delete();

    $discount = Discount::factory()->create();
    $collection = Collection::factory()->create();

    $brand->discounts()->attach($discount);
    $brand->collections()->attach($collection);

    assertDatabaseHas($brand->discounts()->getTable(), [
        'brand_id' => $brand->id,
        'discount_id' => $discount->id,
    ]);

    assertDatabaseHas($brand->collections()->getTable(), [
        'brand_id' => $brand->id,
        'collection_id' => $collection->id,
    ]);

    $brand->delete();

    assertDatabaseMissing($brand->discounts()->getTable(), [
        'brand_id' => $brand->id,
        'discount_id' => $discount->id,
    ]);

    assertDatabaseMissing($brand->collections()->getTable(), [
        'brand_id' => $brand->id,
        'collection_id' => $collection->id,
    ]);

    assertDatabaseMissing(Brand::class, [
        'id' => $brand->id,
    ]);

    assertDatabaseHas(Product::class, [
        'id' => $activeProduct->id,
        'brand_id' => null,
    ]);

    assertDatabaseHas(Product::class, [
        'id' => $trashedProduct->id,
        'brand_id' => null,
    ]);
});
