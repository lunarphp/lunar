<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Lunar\Core\Media\StandardDefinitions as StandardMediaDefinitions;
use Lunar\Core\Models\Product;
use Lunar\Tests\Core\Stubs\TestStandardMediaDefinitions;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);

uses(RefreshDatabase::class);

test('conversions are loaded', function () {
    config()->set('media-library.queue_conversions_by_default', false);

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
