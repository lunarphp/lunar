<?php

uses(\Lunar\Tests\Core\TestCase::class);

use Lunar\Facades\DB;
use Lunar\Models\Brand;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->image = __DIR__.'/../../Stubs/images/converse.jpg';
    $this->imageCollection = config('lunar.media.collection');
});

test('can only have one primary media for "images" collection', function () {
    $brand = Brand::factory()->create();

    $image1 = $brand
        ->addMedia($this->image)
        ->preservingOriginal()
        ->withCustomProperties([
            'primary' => true,
        ])
        ->toMediaCollection($this->imageCollection);

    expect($image1->getCustomProperty('primary'))->toBeTrue();

    $image2 = $brand
        ->addMedia($this->image)
        ->preservingOriginal()
        ->withCustomProperties([
            'primary' => true,
        ])
        ->toMediaCollection($this->imageCollection);

    expect($image2->getCustomProperty('primary'))->toBeTrue();
    expect($image1->refresh()->getCustomProperty('primary'))->toBeFalse();

    $default1 = $brand
        ->addMedia($this->image)
        ->preservingOriginal()
        ->withCustomProperties([
            'primary' => true,
        ])
        ->toMediaCollection('default');

    expect($default1->getCustomProperty('primary'))->toBeTrue();
    expect($image2->refresh()->getCustomProperty('primary'))->toBeTrue();

    $default2 = $brand
        ->addMedia($this->image)
        ->preservingOriginal()
        ->withCustomProperties([
            'primary' => true,
        ])
        ->toMediaCollection('default');

    expect($image2->refresh()->getCustomProperty('primary'))->toBeTrue();
    expect($image1->refresh()->getCustomProperty('primary'))->toBeFalse();
    expect($default2->getCustomProperty('primary'))->toBeTrue();

    // depends the desired not `images` collection behavior
    // expect($default1->refresh()->getCustomProperty('primary'))->toBeFalse();
});

test('only apply primary media for "images" collection', function () {
    $brand = Brand::factory()->create();

    $image1 = $brand
        ->addMedia($this->image)
        ->preservingOriginal()
        ->withCustomProperties([
            'primary' => true,
        ])
        ->toMediaCollection($this->imageCollection);

    expect($image1->getCustomProperty('primary'))->toBeTrue();

    $image2 = $brand
        ->addMedia($this->image)
        ->preservingOriginal()
        ->withCustomProperties([
            'primary' => true,
        ])
        ->toMediaCollection($this->imageCollection);

    expect($image2->getCustomProperty('primary'))->toBeTrue();
    expect($image1->refresh()->getCustomProperty('primary'))->toBeFalse();

    $default1 = $brand
        ->addMedia($this->image)
        ->preservingOriginal()
        ->withCustomProperties([
            'primary' => true,
        ])
        ->toMediaCollection('default');

    expect($default1->getCustomProperty('primary'))->toBeTrue();
    expect($image2->refresh()->getCustomProperty('primary'))->toBeTrue();

    $default2 = $brand
        ->addMedia($this->image)
        ->preservingOriginal()
        ->withCustomProperties([
            'primary' => true,
        ])
        ->toMediaCollection('default');

    expect($image2->refresh()->getCustomProperty('primary'))->toBeTrue();
    expect($image1->refresh()->getCustomProperty('primary'))->toBeFalse();
    expect($default2->getCustomProperty('primary'))->toBeTrue();
    expect($default1->refresh()->getCustomProperty('primary'))->toBeTrue();
});

test('new primary is selected when current is deleted', function () {
    $brand = Brand::factory()->create();

    $image1 = $brand
        ->addMedia($this->image)
        ->preservingOriginal()
        ->withCustomProperties([
            'primary' => true,
        ])
        ->toMediaCollection($this->imageCollection);

    $image2 = $brand
        ->addMedia($this->image)
        ->preservingOriginal()
        ->withCustomProperties([
            'primary' => true,
        ])
        ->toMediaCollection($this->imageCollection);

    expect($image2->getCustomProperty('primary'))->toBeTrue();
    expect($image1->refresh()->getCustomProperty('primary'))->toBeFalse();

    $image2->delete();
    $this->assertModelMissing($image2);
    expect($image1->refresh()->getCustomProperty('primary'))->toBeTrue();
});

test('auto recover more than 1 primary media', function ($isPrimary) {
    $brand = Brand::factory()->create();

    $images = [];

    for ($x = 0; $x < 3; $x++) {
        $images[] = $brand
            ->addMedia($this->image)
            ->preservingOriginal()
            ->toMediaCollection($this->imageCollection);
    }

    $mediaTable = (new Media)->getTable();

    DB::table($mediaTable)
        ->update([
            'custom_properties' => ['primary' => true],
        ]);

    $brand
        ->addMedia($this->image)
        ->preservingOriginal()
        ->withCustomProperties([
            'primary' => $isPrimary,
        ])
        ->toMediaCollection($this->imageCollection);

    expect(Media::where('custom_properties->primary', true)->count())->toEqual(1);
    expect(Media::where('custom_properties->primary', false)->count())->toEqual(3);
})->with([
    'new primary' => true,
    'new not primary' => false,
]);

test('set other media primary if current not primary', function () {
    $brand = Brand::factory()->create();

    $images = [];

    for ($x = 0; $x < 3; $x++) {
        $images[] = $brand
            ->addMedia($this->image)
            ->preservingOriginal()
            ->toMediaCollection($this->imageCollection);
    }

    $mediaTable = (new Media)->getTable();

    DB::table($mediaTable)
        ->update([
            'custom_properties' => ['primary' => false],
        ]);

    $image = $brand
        ->addMedia($this->image)
        ->preservingOriginal()
        ->withCustomProperties([
            'primary' => false,
        ])
        ->toMediaCollection($this->imageCollection);

    expect($image->getCustomProperty('primary'))->toBeFalse();
    expect(Media::where('custom_properties->primary', false)->count())->toEqual(3);
    expect(Media::where('custom_properties->primary', true)->count())->toEqual(1);
});

test('set current media primary if no existing primary', function () {
    $brand = Brand::factory()->create();

    $image = $brand
        ->addMedia($this->image)
        ->preservingOriginal()
        ->toMediaCollection($this->imageCollection);

    expect($image->refresh()->getCustomProperty('primary'))->toBeTrue();
    expect(Media::where('custom_properties->primary', true)->count())->toEqual(1);
});

test('can delete last media', function () {
    $brand = Brand::factory()->create();

    $image = $brand
        ->addMedia($this->image)
        ->preservingOriginal()
        ->withCustomProperties([
            'primary' => false,
        ])
        ->toMediaCollection($this->imageCollection);

    expect(Media::count())->toEqual(1);
    $image->delete();
    expect(Media::count())->toEqual(0);
    $this->assertModelMissing($image);
});
