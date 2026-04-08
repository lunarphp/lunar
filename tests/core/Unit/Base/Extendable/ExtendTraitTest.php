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

test('can override scout should be searchable method', function () {

    $product = Product::find(1);
    expect($product->shouldBeSearchable())->toBeFalse();
});
