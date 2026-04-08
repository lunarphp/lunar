<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Lunar\Facades\DB;
use Lunar\Models\Brand;
use Lunar\Tests\Core\TestCase;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

uses(TestCase::class)->group('media.observer');

uses(RefreshDatabase::class);

test('can only have one primary media for "images" collection', function () {
    $brand = Brand::factory()->create();

    $image = UploadedFile::fake()->image('foobar.jpg');

    $image1 = $brand
        ->addMedia($image)
        ->preservingOriginal()
        ->withCustomProperties([
            'primary' => true,
        ])
        ->toMediaCollection('images');

    expect($image1->getCustomProperty('primary'))->toBeTrue();

    $image2 = $brand
        ->addMedia($image)
        ->preservingOriginal()
        ->withCustomProperties([
            'primary' => true,
        ])
        ->toMediaCollection('images');

    expect($image2->getCustomProperty('primary'))
        ->toBeTrue()
        ->and($image1->refresh()->getCustomProperty('primary'))
        ->toBeFalse();

    $default1 = $brand
        ->addMedia($image)
        ->preservingOriginal()
        ->withCustomProperties([
            'primary' => true,
        ])
        ->toMediaCollection();

    expect($default1->getCustomProperty('primary'))
        ->toBeTrue()
        ->and($image2->refresh()->getCustomProperty('primary'))
        ->toBeTrue();

    $default2 = $brand
        ->addMedia($image)
        ->preservingOriginal()
        ->withCustomProperties([
            'primary' => true,
        ])
        ->toMediaCollection();

    expect($image2->refresh()->getCustomProperty('primary'))
        ->toBeTrue()
        ->and($image1->refresh()->getCustomProperty('primary'))
        ->toBeFalse()
        ->and($default2->getCustomProperty('primary'))
        ->toBeTrue();

    // depends the desired not `images` collection behavior
    // expect($default1->refresh()->getCustomProperty('primary'))->toBeFalse();
});

test('only apply primary media for "images" collection', function () {
    $brand = Brand::factory()->create();
    $image = UploadedFile::fake()->image('foobar.jpg');

    $image1 = $brand
        ->addMedia($image)
        ->preservingOriginal()
        ->withCustomProperties([
            'primary' => true,
        ])
        ->toMediaCollection('images');

    expect($image1->getCustomProperty('primary'))->toBeTrue();

    $image2 = $brand
        ->addMedia($image)
        ->preservingOriginal()
        ->withCustomProperties([
            'primary' => true,
        ])
        ->toMediaCollection('images');

    expect($image2->getCustomProperty('primary'))
        ->toBeTrue()
        ->and($image1->refresh()->getCustomProperty('primary'))
        ->toBeFalse();

    $default1 = $brand
        ->addMedia($image)
        ->preservingOriginal()
        ->withCustomProperties([
            'primary' => true,
        ])
        ->toMediaCollection();

    expect($default1->getCustomProperty('primary'))
        ->toBeTrue()
        ->and($image2->refresh()->getCustomProperty('primary'))
        ->toBeTrue();

    $default2 = $brand
        ->addMedia($image)
        ->preservingOriginal()
        ->withCustomProperties([
            'primary' => true,
        ])
        ->toMediaCollection();

    expect($image2->refresh()->getCustomProperty('primary'))->toBeTrue()
        ->and($image1->refresh()->getCustomProperty('primary'))->toBeFalse()
        ->and($default2->getCustomProperty('primary'))->toBeTrue()
        ->and($default1->refresh()->getCustomProperty('primary'))->toBeTrue();
});

test('new primary is selected when current is deleted', function () {
    $brand = Brand::factory()->create();
    $image = UploadedFile::fake()->image('foobar.jpg');

    $image1 = $brand
        ->addMedia($image)
        ->preservingOriginal()
        ->withCustomProperties([
            'primary' => true,
        ])
        ->toMediaCollection('images');

    $image2 = $brand
        ->addMedia($image)
        ->preservingOriginal()
        ->withCustomProperties([
            'primary' => true,
        ])
        ->toMediaCollection('images');

    expect($image2->getCustomProperty('primary'))->toBeTrue()
        ->and($image1->refresh()->getCustomProperty('primary'))->toBeFalse();

    $image2->delete();
    $this->assertModelMissing($image2);
    expect($image1->refresh()->getCustomProperty('primary'))->toBeTrue();
});

test('auto recover more than 1 primary media', function ($isPrimary) {
    $brand = Brand::factory()->create();

    $images = [];

    for ($x = 0; $x < 3; $x++) {
        $images[] = $brand
            ->addMedia(
                UploadedFile::fake()->image("{$x}.jpg")
            )
            ->preservingOriginal()
            ->toMediaCollection('images');
    }

    $mediaTable = (new Media)->getTable();

    DB::table($mediaTable)
        ->update([
            'custom_properties' => ['primary' => true],
        ]);

    $brand
        ->addMedia(
            UploadedFile::fake()->image('foobar.jpg')
        )
        ->preservingOriginal()
        ->withCustomProperties([
            'primary' => $isPrimary,
        ])
        ->toMediaCollection('images');

    expect(Media::where('custom_properties->primary', true)->count())->toEqual(1)
        ->and(Media::where('custom_properties->primary', false)->count())->toEqual(3);
})->with([
    'new primary' => true,
    'new not primary' => false,
]);

test('set other media primary if current not primary', function () {
    $brand = Brand::factory()->create();

    for ($x = 0; $x < 3; $x++) {
        $brand
            ->addMedia(
                UploadedFile::fake()->image("{$x}.jpg")
            )
            ->preservingOriginal()
            ->toMediaCollection('images');
    }

    $mediaTable = (new Media)->getTable();

    DB::table($mediaTable)
        ->update([
            'custom_properties' => ['primary' => false],
        ]);

    $image = $brand
        ->addMedia(
            UploadedFile::fake()->image('foobar.jpg')
        )
        ->preservingOriginal()
        ->withCustomProperties([
            'primary' => false,
        ])
        ->toMediaCollection('images');

    expect($image->getCustomProperty('primary'))->toBeFalse()
        ->and(Media::where('custom_properties->primary', false)->count())->toEqual(3)
        ->and(Media::where('custom_properties->primary', true)->count())->toEqual(1);
});

test('set current media primary if no existing primary', function () {
    $brand = Brand::factory()->create();

    $image = $brand
        ->addMedia(
            UploadedFile::fake()->image('foobar.jpg')
        )
        ->preservingOriginal()
        ->toMediaCollection('images');

    expect($image->refresh()->getCustomProperty('primary'))->toBeTrue()
        ->and(Media::where('custom_properties->primary', true)->count())->toEqual(1);
});

test('can delete last media', function () {
    $brand = Brand::factory()->create();

    $image = $brand
        ->addMedia(
            UploadedFile::fake()->image('foobar.jpg')
        )
        ->preservingOriginal()
        ->withCustomProperties([
            'primary' => false,
        ])
        ->toMediaCollection('images');

    expect(Media::count())->toEqual(1);
    $image->delete();
    expect(Media::count())->toEqual(0);
    $this->assertModelMissing($image);
});
