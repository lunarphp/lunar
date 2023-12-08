<?php

uses(\Lunar\Tests\Unit\Base\Extendable\ExtendableTestCase::class);
use Lunar\Models\Product;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('can override scout should be searchable method', function () {
    $product = Product::find(1);
    expect($product->shouldBeSearchable())->toBeFalse();
});
