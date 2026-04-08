<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Facades\ModelManifest;
use Lunar\Models\Product;
use Lunar\Tests\Core\Unit\Base\Extendable\ExtendableTestCase;

uses(ExtendableTestCase::class);

uses(RefreshDatabase::class);

beforeEach(
    fn () => ModelManifest::replace(
        Lunar\Models\Contracts\Product::class,
        Lunar\Tests\Core\Stubs\Models\Product::class
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
