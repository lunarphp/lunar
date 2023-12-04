<?php

uses(\Lunar\Tests\TestCase::class);
use Illuminate\Http\UploadedFile;
use Lunar\Base\StandardMediaConversions;
use Lunar\Models\Product;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('conversions are loaded', function () {
    $conversions = config('lunar.media.conversions');

    expect($conversions)->toHaveCount(1);

    expect($conversions[0])->toEqual(StandardMediaConversions::class);

    $file = UploadedFile::fake()->image('avatar.jpg');

    $product = Product::factory()->create();

    $product->addMedia($file)->toMediaCollection('images');

    $image = $product->images->first();

    expect($image->hasGeneratedConversion('small'))->toBeTrue();
    expect($image->hasGeneratedConversion('medium'))->toBeTrue();
    expect($image->hasGeneratedConversion('large'))->toBeTrue();
    expect($image->hasGeneratedConversion('zoom'))->toBeTrue();
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
