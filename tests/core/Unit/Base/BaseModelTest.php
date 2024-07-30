<?php

uses(\Lunar\Tests\Core\TestCase::class);
use Illuminate\Support\Facades\Route;
use Lunar\Base\BaseModel;
use Lunar\Base\Traits\HasModelExtending;
use Lunar\Models\Collection as ModelsCollection;
use Lunar\Models\Product;
use Lunar\Models\Url;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('model is macroable', function () {
    Product::macro('foo', function () {
        return 'bar';
    });

    $product = Product::factory()->create();

    expect($product->foo())->toEqual('bar');
});

test('macros are scoped to the correct model', function () {
    Route::get('/products/{slug}', function () {
        return '';
    })->name('test.products');

    Route::get('/collections/{slug}', function () {
        return '';
    })->name('test.collections');

    Product::macro('getPermalink', function () {
        /** @var ModelsCollection $this */
        return route('test.products', $this->defaultUrl->slug);
    });

    ModelsCollection::macro('getPermalink', function () {
        /** @var ModelsCollection $this */
        return route('test.collections', $this->defaultUrl->slug);
    });

    $product = Product::factory()->create();

    $collection = ModelsCollection::factory()->create();

    $collection->urls()->create(
        Url::factory()->make([
            'slug' => 'foo-collection',
            'default' => true,
        ])->toArray()
    );

    $product->urls()->create(
        Url::factory()->make([
            'slug' => 'foo-product',
            'default' => true,
        ])->toArray()
    );

    expect($product->getPermalink())->toEqual(route('test.products', $product->defaultUrl->slug));

    expect($collection->getPermalink())->toEqual(route('test.collections', $collection->defaultUrl->slug));
});

test('base model includes trait', function () {
    $uses = class_uses_recursive(BaseModel::class);
    expect(in_array(HasModelExtending::class, $uses))->toBeTrue();
});
