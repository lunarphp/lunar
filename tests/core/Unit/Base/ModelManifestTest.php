<?php

uses(\Lunar\Tests\Core\TestCase::class)->group('model_extending');

use Lunar\Base\ModelManifestInterface;
use Lunar\Facades\ModelManifest;
use Lunar\Models\Product;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('can instantiate class', function () {
    $manifest = app(ModelManifestInterface::class);

    expect($manifest)->toBeInstanceOf(\Lunar\Base\ModelManifest::class);
});

test('can add model', function () {
    ModelManifest::add(
        \Lunar\Models\Contracts\Product::class,
        \Lunar\Tests\Core\Stubs\Models\Product::class,
    );

    expect(Product::modelClass())->toBe(\Lunar\Tests\Core\Stubs\Models\Product::class);
});

test('can replace model', function () {
    ModelManifest::replace(
        \Lunar\Models\Contracts\Product::class,
        \Lunar\Tests\Core\Stubs\Models\Product::class,
    );

    expect(Product::modelClass())->toBe(\Lunar\Tests\Core\Stubs\Models\Product::class);
});

test('can get registered model', function () {
    expect(
        ModelManifest::get(\Lunar\Models\Contracts\Product::class)
    )->toBe(Product::class);

    ModelManifest::replace(
        \Lunar\Models\Contracts\Product::class,
        \Lunar\Tests\Core\Stubs\Models\Product::class,
    );

    expect(
        ModelManifest::get(\Lunar\Models\Contracts\Product::class)
    )->toBe(\Lunar\Tests\Core\Stubs\Models\Product::class);
});

test('can guess contract class', function () {
    expect(
        ModelManifest::guessContractClass(Product::class)
    )->toBe(\Lunar\Models\Contracts\Product::class);
});

test('can guess model class', function () {
    expect(
        ModelManifest::guessModelClass(\Lunar\Models\Contracts\Product::class)
    )->toBe(Product::class);
});

test('can detect lunar model', function () {
    expect(
        ModelManifest::isLunarModel((new Product()))
    )->toBeTrue()
        ->and(
            ModelManifest::isLunarModel((new \Lunar\Tests\Core\Stubs\Models\Product()))
        )->toBeFalse();
});

test('can detect table name', function () {
    ModelManifest::replace(
        \Lunar\Models\Contracts\Product::class,
        \Lunar\Tests\Core\Stubs\Models\CustomProduct::class,
    );

    expect(
        ModelManifest::getTable(new \Lunar\Tests\Core\Stubs\Models\CustomProduct())
    )->toBe('products');
});

test('can add additional morph mapping', function () {
    ModelManifest::replace(
        \Lunar\Models\Contracts\Product::class,
        \Lunar\Tests\Core\Stubs\Models\CustomProduct::class,
    );

    expect(
        ModelManifest::getTable(new \Lunar\Tests\Core\Stubs\Models\CustomProduct())
    )->toBe('products');
});
