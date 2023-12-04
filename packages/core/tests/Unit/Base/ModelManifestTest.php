<?php

uses(\Lunar\Tests\TestCase::class);
use Lunar\Base\ModelManifestInterface;
use Lunar\Facades\ModelManifest;
use Lunar\Models\Product;
use Lunar\Models\ProductOption;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('can instantiate class', function () {
    $manifest = app(ModelManifestInterface::class);

    expect($manifest)->toBeInstanceOf(\Lunar\Base\ModelManifest::class);
});

test('can register models', function () {
    expect(ModelManifest::getRegisteredModels())->toHaveCount(0);

    ModelManifest::register(collect([
        Product::class => \Lunar\Tests\Stubs\Models\Product::class,
        ProductOption::class => \Lunar\Tests\Stubs\Models\ProductOption::class,
    ]));

    expect(ModelManifest::getRegisteredModels())->toHaveCount(2);
});

test('can get registered model from base model', function () {
    ModelManifest::register(collect([
        Product::class => \Lunar\Tests\Stubs\Models\Product::class,
    ]));

    $model = ModelManifest::getRegisteredModel(Product::class);

    expect($model)->toBeInstanceOf(\Lunar\Tests\Stubs\Models\Product::class);
});

test('can get morph class base model', function () {
    ModelManifest::register(collect([
        Product::class => \Lunar\Tests\Stubs\Models\Product::class,
    ]));

    $customModels = ModelManifest::getRegisteredModels()->flip();

    expect(value: $customModels->get(\Lunar\Tests\Stubs\Models\Product::class))->toEqual(expected: Product::class);
});

test('can get list of registered base models', function () {
    ModelManifest::register(collect([
        Product::class => \Lunar\Tests\Stubs\Models\Product::class,
        ProductOption::class => \Lunar\Tests\Stubs\Models\ProductOption::class,
    ]));

    expect(ModelManifest::getBaseModelClasses())->toEqual(collect([
        Product::class,
        ProductOption::class,
    ]));
});
