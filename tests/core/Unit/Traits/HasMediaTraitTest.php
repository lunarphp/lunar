<?php


use Illuminate\Http\UploadedFile;
use Lunar\Base\StandardMediaDefinitions;
use Lunar\Models\Product;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);

test('conversions are loaded', function () {
    $definitions = config('lunar.media.definitions');

    expect($definitions)->toHaveCount(6);

    expect($definitions[Product::class])->toEqual(StandardMediaDefinitions::class);

    $file = UploadedFile::fake()->image('avatar.jpg');

    $product = Product::factory()->create();

    $product->addMedia($file)->toMediaCollection('images');

    $image = $product->images->first();

    expect($image->hasGeneratedConversion('small'))->toBeTrue();
    expect($image->hasGeneratedConversion('medium'))->toBeTrue();
    expect($image->hasGeneratedConversion('large'))->toBeTrue();
    expect($image->hasGeneratedConversion('zoom'))->toBeTrue();
})->skipOnPhp('<=8.1');

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
