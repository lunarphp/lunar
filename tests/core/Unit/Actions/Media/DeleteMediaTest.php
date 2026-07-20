<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Lunar\Core\Actions\Media\DeleteMedia;
use Lunar\Core\Models\Brand;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class)->group('media.actions');
uses(RefreshDatabase::class);

test('deletes the media and re-points the primary flag', function () {
    $brand = Brand::factory()->create();

    $collection = config('lunar.media.collection');

    $first = $brand->addMedia(UploadedFile::fake()->image('one.jpg'))->toMediaCollection($collection);
    $second = $brand->addMedia(UploadedFile::fake()->image('two.jpg'))->toMediaCollection($collection);

    expect($first->refresh()->getCustomProperty('primary'))->toBeTrue();

    app(DeleteMedia::class)->execute($first);

    expect($brand->refresh()->getMedia($collection))->toHaveCount(1)
        ->and($second->refresh()->getCustomProperty('primary'))->toBeTrue();
});
