<?php

uses(\Lunar\Tests\Core\Unit\Base\Extendable\ExtendableTestCase::class);

use Lunar\Facades\ModelManifest;
use Lunar\Models\Product;
use Lunar\Models\Url;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    ModelManifest::replace(
        \Lunar\Models\Contracts\Product::class,
        \Lunar\Tests\Core\Stubs\Models\Product::class
    );

    $this->product = \Lunar\Tests\Core\Stubs\Models\Product::query()->create(
        Product::factory()->raw()
    );
});

test('can get url morph relation when using extended model', function () {
    $productUrl = $this->product->urls()->create([
        'slug' => 'foo-product',
        'default' => true,
        'language_id' => 1,
    ]);

    $this->assertDatabaseHas((new Url)->getTable(), [
        'element_type' => $this->product->getMorphClass(),
        'element_id' => $productUrl->element_id,
    ]);
    expect($this->product->defaultUrl)->toBeInstanceOf(Url::class);
});

test('can get media thumbnail morph relation when using extended model', function () {
    $this->expectNotToPerformAssertions();
});

test('can get prices relation when using extended model', function () {
    $this->expectNotToPerformAssertions();
});

test('can return the correct morph class when using enforce morph map', function () {
    $this->expectNotToPerformAssertions();
});
