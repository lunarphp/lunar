<?php

uses(\Lunar\Tests\Core\TestCase::class);
use Illuminate\Support\Facades\Config;
use Lunar\Generators\UrlGenerator;
use Lunar\Models\Brand;
use Lunar\Models\Language;
use Lunar\Models\Url;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

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
        'element_type' => Brand::class,
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
    \Lunar\Models\Attribute::factory()->create([
        'attribute_type' => Brand::class,
    ]);
    $brand = Brand::factory()->create([
        'name' => 'Test Brand',
    ]);
    expect($brand->mappedAttributes)->toHaveCount(1);
});
