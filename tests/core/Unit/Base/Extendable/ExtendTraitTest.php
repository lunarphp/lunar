<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Facades\ModelManifest;
use Lunar\Core\Models\Product;
use Lunar\Tests\Core\Unit\Base\Extendable\ExtendableTestCase;

uses(ExtendableTestCase::class);

uses(RefreshDatabase::class);

beforeEach(
    fn () => ModelManifest::replace(
        Lunar\Core\Models\Contracts\Product::class,
        Lunar\Tests\Core\Stubs\Models\Product::class
    )
);

test('can override scout should be searchable method', function () {

    $product = Product::find(1);
    expect($product->shouldBeSearchable())->toBeFalse();
});
