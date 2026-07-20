<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Lunar\Core\Actions\Media\UpdateMedia;
use Lunar\Core\Models\Brand;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class)->group('media.actions');
uses(RefreshDatabase::class);

beforeEach(function () {
    $this->brand = Brand::factory()->create();

    $this->media = $this->brand
        ->addMedia(UploadedFile::fake()->image('one.jpg'))
        ->toMediaCollection(config('lunar.media.collection'));
});

test('updates name, alt, caption and focal custom properties', function () {
    app(UpdateMedia::class)->execute($this->media, [
        'name' => 'Hero shot',
        'alt' => 'Front of the product',
        'caption' => 'Shot in the studio.',
        'focal' => ['x' => 25, 'y' => 75],
    ]);

    $this->media->refresh();

    expect($this->media->getCustomProperty('name'))->toBe('Hero shot')
        ->and($this->media->getCustomProperty('alt'))->toBe('Front of the product')
        ->and($this->media->getCustomProperty('caption'))->toBe('Shot in the studio.')
        ->and($this->media->getCustomProperty('focal'))->toBe(['x' => 25, 'y' => 75]);
});

test('clamps the focal point to 0-100', function () {
    app(UpdateMedia::class)->execute($this->media, [
        'focal' => ['x' => -20, 'y' => 140],
    ]);

    expect($this->media->refresh()->getCustomProperty('focal'))->toBe(['x' => 0, 'y' => 100]);
});

test('leaves manipulations untouched when no conversion crops', function () {
    app(UpdateMedia::class)->execute($this->media, [
        'focal' => ['x' => 50, 'y' => 50],
    ]);

    expect($this->media->refresh()->manipulations)->toBe([]);
});

test('promoting to primary demotes the current primary', function () {
    $second = $this->brand
        ->addMedia(UploadedFile::fake()->image('two.jpg'))
        ->toMediaCollection(config('lunar.media.collection'));

    expect($this->media->refresh()->getCustomProperty('primary'))->toBeTrue();

    app(UpdateMedia::class)->execute($second, ['primary' => true]);

    expect($second->refresh()->getCustomProperty('primary'))->toBeTrue()
        ->and($this->media->refresh()->getCustomProperty('primary'))->toBeFalse();
});
