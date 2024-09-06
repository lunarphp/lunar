<?php

uses(\Lunar\Tests\Core\Unit\Base\Extendable\ExtendableTestCase::class);

use Lunar\Models\Product;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(
    fn () => \Lunar\Facades\ModelManifest::replace(
        \Lunar\Models\Contracts\Product::class,
        \Lunar\Tests\Core\Stubs\Models\Product::class
    )
);

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
    expect($product->shouldBeSearchable())->toBeFalse();
});
