<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Lunar\Core\Exceptions\BrandActionException;
use Lunar\Core\Generators\UrlGenerator;
use Lunar\Core\Models\Attribute;
use Lunar\Core\Models\AttributeModel;
use Lunar\Core\Models\Brand;
use Lunar\Core\Models\Collection;
use Lunar\Core\Models\Discount;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\Url;
use Lunar\Core\States\Brand\Active;
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

test('defaults to the active state', function () {
    $brand = Brand::create(['name' => 'Test Brand', 'handle' => 'test-brand']);

    expect($brand->status)->toBeInstanceOf(Active::class);
});

test('active scope filters out draft brands', function () {
    Brand::factory()->active()->create();
    Brand::factory()->draft()->create();

    expect(Brand::active()->count())->toBe(1);
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
    Attribute::factory()
        ->has(AttributeModel::factory()->state(['model_type' => 'brand']), 'models')
        ->create();

    $brand = Brand::factory()->create([
        'name' => 'Test Brand',
    ]);
    expect($brand->mappedAttributes)->toHaveCount(1);
});

test('can delete a brand without products', function () {
    $brand = Brand::factory()->create([
        'name' => 'Test Brand',
    ]);

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
});

test('refuses to delete a brand with products on any path', function () {
    $brand = Brand::factory()->create([
        'name' => 'Test Brand',
    ]);

    $product = Product::factory()->create([
        'brand_id' => $brand->id,
    ]);

    expect(fn () => $brand->delete())
        ->toThrow(BrandActionException::class);

    assertDatabaseHas(Brand::class, ['id' => $brand->id]);
    assertDatabaseHas(Product::class, ['id' => $product->id, 'brand_id' => $brand->id]);

    // Reassigning the product unblocks deletion.
    $product->update(['brand_id' => null]);
    $brand->refresh()->delete();

    assertDatabaseMissing(Brand::class, ['id' => $brand->id]);
});
