<?php

uses(\Lunar\Tests\Core\Unit\Base\Extendable\ExtendableTestCase::class)->group('model_extending');

use Illuminate\Support\Collection;
use Lunar\Models\Product;
use Lunar\Models\ProductOption;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(
    function () {
        \Lunar\Facades\ModelManifest::replace(
            \Lunar\Models\Contracts\Product::class,
            \Lunar\Tests\Core\Stubs\Models\Product::class
        );

        \Lunar\Facades\ModelManifest::replace(
            \Lunar\Models\Contracts\ProductOption::class,
            \Lunar\Tests\Core\Stubs\Models\ProductOption::class
        );
    }
);

test('can get new instance of the registered model', function () {
    $product = Product::find(1);

    expect($product)->toBeInstanceOf(\Lunar\Tests\Core\Stubs\Models\Product::class);
});

test('can forward calls to extended model', function () {
    // @phpstan-ignore-next-line
    $sizeOption = ProductOption::with('sizes')->find(1);

    expect($sizeOption)->toBeInstanceOf(\Lunar\Tests\Core\Stubs\Models\ProductOption::class);

    expect($sizeOption->sizes)->toBeInstanceOf(Collection::class);
    expect($sizeOption->sizes)->toHaveCount(1);
});

test('can forward static method calls to extended model', function () {
    /** @see \Lunar\Tests\Core\Stubs\Models\ProductOption::getSizesStatic() */
    $newStaticMethod = ProductOption::getSizesStatic();

    expect($newStaticMethod)->toBeInstanceOf(Collection::class);
    expect($newStaticMethod)->toHaveCount(3);
});

test('morph map is correct when models are extended', function () {
    \Lunar\Facades\ModelManifest::replace(
        \Lunar\Models\Contracts\Product::class,
        \Lunar\Tests\Core\Stubs\Models\CustomProduct::class
    );

   expect((new \Lunar\Tests\Core\Stubs\Models\CustomProduct)->getMorphClass())
       ->toBe('product')
       ->and((new Product)->getMorphClass())
       ->toBe('product');
});

