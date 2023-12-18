<?php

uses(\Unit\Base\Extendable\ExtendableTestCase::class);

use Illuminate\Support\Collection;
use Lunar\Models\Product;
use Lunar\Models\ProductOption;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('can get new instance of the registered model', function () {
    $product = Product::find(1);

    expect($product)->toBeInstanceOf(\Stubs\Models\Product::class);
});

test('can forward calls to extended model', function () {
    // @phpstan-ignore-next-line
    $sizeOption = ProductOption::with('sizes')->find(1);

    expect($sizeOption)->toBeInstanceOf(\Stubs\Models\ProductOption::class);

    expect($sizeOption->sizes)->toBeInstanceOf(Collection::class);
    expect($sizeOption->sizes)->toHaveCount(1);
});

test('can forward static method calls to extended model', function () {
    /** @see \Stubs\Models\ProductOption::getSizesStatic() */
    $newStaticMethod = ProductOption::getSizesStatic();

    expect($newStaticMethod)->toBeInstanceOf(Collection::class);
    expect($newStaticMethod)->toHaveCount(3);
});

test('can swap registered model implementation', function () {
    /** @var Product $product */
    $product = Product::find(1);

    $newProductModel = $product->swap(
        \Stubs\Models\ProductSwapModel::class
    );

    expect($product)->toBeInstanceOf(\Stubs\Models\Product::class);
    expect($newProductModel)->toBeInstanceOf(\Stubs\Models\ProductSwapModel::class);
});

test('can get base model morph class name', function () {
    $product = \Stubs\Models\Product::query()->create(
        Product::factory()->raw()
    );

    expect($product->getMorphClass())->toEqual(Product::class);
});
