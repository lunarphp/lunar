<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Lunar\Core\Actions\Media\ReorderMedia;
use Lunar\Core\Models\Brand;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class)->group('media.actions');
uses(RefreshDatabase::class);

beforeEach(function () {
    $this->brand = Brand::factory()->create();

    $this->media = collect(['one', 'two', 'three'])->map(
        fn (string $name) => $this->brand
            ->addMedia(UploadedFile::fake()->image("{$name}.jpg"))
            ->toMediaCollection(config('lunar.media.collection'))
    );
});

test('persists the new order and promotes the first item to primary', function () {
    $reversed = $this->media->reverse()->values();

    app(ReorderMedia::class)->execute($this->brand, $reversed->pluck('id')->all());

    $ordered = $this->brand->refresh()->getMedia(config('lunar.media.collection'));

    expect($ordered->pluck('id')->all())->toBe($reversed->pluck('id')->all())
        ->and($ordered->first()->getCustomProperty('primary'))->toBeTrue()
        ->and($this->media->first()->refresh()->getCustomProperty('primary'))->toBeFalse();
});

test('rejects an id set that does not match the collection', function () {
    app(ReorderMedia::class)->execute($this->brand, [999]);
})->throws(InvalidArgumentException::class);
