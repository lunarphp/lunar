<?php

uses(\Lunar\Tests\Core\TestCase::class);

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Lunar\FieldTypes\Text;
use Lunar\Generators\UrlGenerator;
use Lunar\Models\Brand;
use Lunar\Models\Language;
use Lunar\Models\Product;
use Lunar\Models\Url;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('urls dont generate by default', function () {
    $product = Product::factory()->create();

    expect($product->refresh()->urls)->toHaveCount(0);

    $this->assertDatabaseMissing((new Url)->getTable(), [
        'element_type' => $product->getMorphClass(),
        'element_id' => $product->id,
    ]);
});

/** @test * */
function can_generate_urls()
{
    Language::factory()->create(['default' => true]);

    Config::set('lunar.urls.generator', UrlGenerator::class);

    $product = Product::factory()->create();

    expect($product->refresh()->urls)->toHaveCount(1);

    \Pest\Laravel\assertDatabaseHas((new Url)->getTable(), [
        'element_type' => $product->getMorphClass(),
        'element_id' => $product->id,
        'slug' => Str::slug($product->translateAttribute('name')),
    ]);
}

test('generates unique urls', function () {
    Language::factory()->create(['default' => true]);

    Config::set('lunar.urls.generator', UrlGenerator::class);

    $product1 = Product::factory()->create([
        'attribute_data' => collect([
            'name' => new Text('Test Product'),
        ]),
    ]);

    $product2 = Product::factory()->create([
        'attribute_data' => collect([
            'name' => new Text('Test Product'),
        ]),
    ]);

    $this->assertNotEquals(
        $product1->urls->first()->slug,
        $product2->urls->first()->slug
    );
});

test('prefers column name over attribute name', function () {

    Language::factory()->create(['default' => true]);

    Config::set('lunar.urls.generator', UrlGenerator::class);

    $brand = Brand::create([
        'name' => 'Column Brand Name',
        'attribute_data' => [
            'name' => new Text('Attribute Brand Name'),
            'type' => new Text('Some Type'),
        ],
    ]);

    expect($brand->defaultUrl)
        ->first()->slug->toBe('column-brand-name');
});

test('column_will_be_used_if_not_set_in_attributes', function () {

    Language::factory()->create(['default' => true]);

    Config::set('lunar.urls.generator', UrlGenerator::class);

    $brand = Brand::create([
        'name' => 'Column Brand Name',
        'attribute_data' => [
            'type' => new Text('Some Type'),
        ],
    ]);

    expect($brand->defaultUrl)
        ->first()->slug->toBe('column-brand-name');
});
