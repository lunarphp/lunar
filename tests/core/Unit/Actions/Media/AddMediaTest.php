<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Lunar\Core\Actions\Media\AddMedia;
use Lunar\Core\Models\Brand;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class)->group('media.actions');
uses(RefreshDatabase::class);

test('attaches an upload to the configured collection with custom properties', function () {
    $brand = Brand::factory()->create();

    $media = app(AddMedia::class)->execute(
        $brand,
        UploadedFile::fake()->image('logo.jpg'),
        customProperties: ['name' => 'Logo', 'alt' => 'The logo'],
    );

    expect($media->collection_name)->toBe(config('lunar.media.collection'))
        ->and($media->getCustomProperty('name'))->toBe('Logo')
        ->and($media->getCustomProperty('alt'))->toBe('The logo')
        ->and($media->refresh()->getCustomProperty('primary'))->toBeTrue();
});
