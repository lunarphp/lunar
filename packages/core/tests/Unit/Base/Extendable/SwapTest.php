<?php

uses(\Lunar\Tests\Unit\Base\Extendable\ExtendableTestCase::class);
use Lunar\Models\Product;
use Lunar\Tests\Stubs\Models\ProductSwapModel;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('core model made aware of swapping instances', function () {
    $this->product = Product::find(1);
    $this->product->swap(ProductSwapModel::class);

    $this->product = Product::find(3);
    expect($this->product->shouldBeSearchable())->toBeFalse();
});
