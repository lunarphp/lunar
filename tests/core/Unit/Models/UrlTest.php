<?php

uses(\Lunar\Tests\Core\TestCase::class);
use Illuminate\Support\Str;
use Lunar\Models\Language;
use Lunar\Models\Product;
use Lunar\Models\Url;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('can create a url', function () {
    $product = Product::factory()->create();
    $language = Language::factory()->create();

    $data = [
        'language_id' => $language->id,
        'element_id' => $product->id,
        'element_type' => Product::class,
        'slug' => Str::slug($product->translateAttribute('name')),
        'default' => true,
    ];

    Url::create($data);

    $this->assertDatabaseHas('lunar_urls', $data);
});

test('can fetch element from url relationship', function () {
    $product = Product::factory()->create();
    $language = Language::factory()->create();

    $data = [
        'language_id' => $language->id,
        'element_id' => $product->id,
        'element_type' => Product::class,
        'slug' => Str::slug($product->translateAttribute('name')),
        'default' => true,
    ];

    $url = Url::create($data);

    expect($url->element)->toBeInstanceOf(Product::class);
    expect($url->element->id)->toEqual($product->id);
});
