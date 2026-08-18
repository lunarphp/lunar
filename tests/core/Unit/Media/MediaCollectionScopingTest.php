<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Lunar\Core\Actions\Media\ReorderMedia;
use Lunar\Core\Models\Brand;
use Lunar\Tests\Core\Stubs\TestMediaGroupDefinitions;
use Lunar\Tests\Core\TestCase;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileUnacceptableForCollection;

use function Pest\Laravel\assertDatabaseCount;

uses(TestCase::class)->group('media.actions');
uses(RefreshDatabase::class);

beforeEach(function () {
    Config::set('lunar.media.definitions', [
        'brand' => TestMediaGroupDefinitions::class,
    ]);

    $this->brand = Brand::factory()->create();
});

test('the image collection rejects a non-image mime type', function () {
    $this->brand
        ->addMedia(UploadedFile::fake()->createWithContent('spec.pdf', "%PDF-1.4\n1 0 obj<<>>endobj\ntrailer<<>>\n%%EOF"))
        ->toMediaCollection(config('lunar.media.collection'));
})->throws(FileUnacceptableForCollection::class);

test('reordering a non-image collection does not promote a primary', function () {
    $media = collect(['a', 'b', 'c'])->map(
        fn (string $name) => $this->brand
            ->addMedia(UploadedFile::fake()->createWithContent("{$name}.pdf", "%PDF-1.4\n1 0 obj<<>>endobj\ntrailer<<>>\n%%EOF"))
            ->toMediaCollection('downloads')
    );

    $reversed = $media->reverse()->values();

    app(ReorderMedia::class)->execute($this->brand, $reversed->pluck('id')->all(), 'downloads');

    $ordered = $this->brand->refresh()->getMedia('downloads');

    expect($ordered->pluck('id')->all())->toBe($reversed->pluck('id')->all())
        ->and($ordered->every(fn ($item) => $item->getCustomProperty('primary') !== true))->toBeTrue();
});

test('thumbnail ignores media outside the image collection', function () {
    $this->brand
        ->addMedia(UploadedFile::fake()->createWithContent('spec.pdf', "%PDF-1.4\n1 0 obj<<>>endobj\ntrailer<<>>\n%%EOF"))
        ->withCustomProperties(['primary' => true])
        ->toMediaCollection('downloads');

    assertDatabaseCount('media', 1);

    expect($this->brand->refresh()->thumbnail)->toBeNull();
});

test('thumbnail resolves media in the image collection', function () {
    $this->brand
        ->addMedia(UploadedFile::fake()->image('hero.jpg'))
        ->toMediaCollection(config('lunar.media.collection'));

    $thumbnail = $this->brand->refresh()->thumbnail;

    expect($thumbnail)->not->toBeNull()
        ->and($thumbnail->collection_name)->toBe(config('lunar.media.collection'))
        ->and($thumbnail->getCustomProperty('primary'))->toBeTrue();
});
