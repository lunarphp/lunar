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

test('can override scout should be searchable method', function () {

    $product = Product::find(1);
    expect($product->shouldBeSearchable())->toBeFalse();
});
