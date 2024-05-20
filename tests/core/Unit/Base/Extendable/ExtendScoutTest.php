<?php

uses(\Lunar\Tests\Core\Unit\Base\Extendable\ExtendableTestCase::class);

use Lunar\Models\Product;
use Lunar\Tests\Core\Stubs\Models\ProductSwapModel;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('can add new scout call via extended model trait', function () {
    $product = Product::find(1);
    expect($product->shouldBeSomethingElseSearchable())->toBeFalse();
});

test('can method be overridden with new instance on runtime', function () {
    $product = Product::find(1);
    expect($product->shouldBeSearchable())->toBeFalse();
});

test('can swap scout call with extended model', function () {
    $product = Product::find(1);
    expect($product->swap(ProductSwapModel::class)->shouldBeSearchable())->toBeFalse();
});
