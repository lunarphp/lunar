<?php

uses(\Lunar\Tests\Core\TestCase::class);
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Lunar\Base\StandardMediaDefinitions;
use Lunar\Models\Product;
use Lunar\Tests\Core\Stubs\TestStandardMediaDefinitions;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('conversions are loaded', function () {
    $definitions = config('lunar.media.definitions');

    expect($definitions)->toHaveCount(6);

    expect($definitions['product'])->toEqual(StandardMediaDefinitions::class);

    $file = UploadedFile::fake()->image('avatar.jpg');

    $product = Product::factory()->create();

    $product->addMedia($file)->toMediaCollection(config('lunar.media.collection'));

    $image = $product->images->first();

    expect($image->hasGeneratedConversion('small'))->toBeTrue();
    expect($image->hasGeneratedConversion('medium'))->toBeTrue();
    expect($image->hasGeneratedConversion('large'))->toBeTrue();
    expect($image->hasGeneratedConversion('zoom'))->toBeTrue();
});

test('custom conversions are loaded', function () {
    Config::set('lunar.media.definitions', [
        'product' => TestStandardMediaDefinitions::class,
    ]);

    $product = invade(new Product);

    expect($product->getDefinitionClass())->toEqual(TestStandardMediaDefinitions::class);
});

test('custom conversions are loaded for extended model', function () {
    \Lunar\Facades\ModelManifest::replace(
        \Lunar\Models\Contracts\Product::class,
        \Lunar\Tests\Core\Stubs\Models\Product::class
    );

    Config::set('lunar.media.definitions', [
        'product' => TestStandardMediaDefinitions::class,
    ]);

    $product = invade(app(\Lunar\Models\Contracts\Product::class));

    expect($product->getDefinitionClass())->toEqual(TestStandardMediaDefinitions::class);
});

test('images can have fallback url', function () {
    $testImageUrl = 'https://picsum.photos/200';
    config()->set('lunar.media.fallback.url', $testImageUrl);

    $product = Product::factory()->create();

    expect($testImageUrl)->toEqual($product->getFirstMediaUrl('images'));
});

test('images can have fallback path', function () {
    $testImagePath = public_path('test.jpg');
    config()->set('lunar.media.fallback.path', $testImagePath);

    $product = Product::factory()->create();

    expect($testImagePath)->toEqual($product->getFirstMediaPath('images'));
});
