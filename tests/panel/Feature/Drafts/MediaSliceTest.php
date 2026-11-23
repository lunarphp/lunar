<?php

use Illuminate\Http\UploadedFile;
use Inertia\Testing\AssertableInertia as Assert;
use Lunar\Core\Models\Brand;
use Lunar\Core\Models\Collection;
use Lunar\Core\Models\CollectionGroup;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductType;
use Lunar\Core\Models\Staff;
use Lunar\Panel\Models\EditDraft;
use Lunar\Tests\Panel\TestCase;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

uses(TestCase::class);

beforeEach(function () {
    $this->staff = Staff::factory()->create(['admin' => true]);
    $this->actingAs($this->staff, 'staff');

    Language::factory()->create(['default' => true, 'code' => 'en']);

    $this->brand = Brand::factory()->create();
    $this->images = config('lunar.media.collection');
});

/** Upload through the staging endpoint and return the staged ids. */
function stageImages(Brand $brand, string ...$names): array
{
    $response = test()->postJson(route('panel.brands.media.store', $brand), [
        'files' => array_map(fn (string $name) => UploadedFile::fake()->image($name, 400, 400), $names),
    ])->assertOk();

    return array_column($response->json('items'), 'id');
}

function imageRow(int $id, array $properties = []): array
{
    return ['id' => $id, 'name' => null, 'alt' => null, 'caption' => null, 'focal' => null, ...$properties];
}

function commitBrandMedia(Brand $brand, array $rows)
{
    return test()->postJson(route('panel.brands.draft.commit', $brand), [
        'data' => ['media:'.config('lunar.media.collection') => $rows],
        'rebase' => [],
    ]);
}

/** @return array<int, array{string, ?string, bool}> file name, alt, primary per brand image in order */
function brandImages(Brand $brand): array
{
    return $brand->refresh()->getMedia(config('lunar.media.collection'))
        ->map(fn (Media $media) => [$media->file_name, $media->getCustomProperty('alt'), (bool) $media->getCustomProperty('primary')])
        ->values()
        ->all();
}

it('stages uploads on the draft, out of the record', function () {
    $ids = stageImages($this->brand, 'one.jpg', 'two.jpg');

    $draft = EditDraft::sole();

    expect($ids)->toHaveCount(2)
        ->and($draft->getMedia($this->images)->modelKeys())->toBe($ids)
        ->and($draft->version)->toBe(0)
        ->and($this->brand->refresh()->getMedia($this->images))->toHaveCount(0);

    // The edit page previews staged items alongside the record's own.
    $this->get(route('panel.brands.edit', $this->brand))
        ->assertInertia(fn (Assert $page) => $page
            ->where('mediaGroups.0.library.0.id', $ids[0])
            ->where('formSliceValues.media:'.$this->images, [])
        );
});

it('rejects non-image uploads to the image collection', function () {
    $this->postJson(route('panel.brands.media.store', $this->brand), [
        'files' => [UploadedFile::fake()->create('notes.pdf', 10, 'application/pdf')],
    ])->assertJsonValidationErrors('files.0');

    expect(EditDraft::count())->toBe(0);
});

it('moves staged uploads onto the record on commit, in order with their properties', function () {
    [$one, $two] = stageImages($this->brand, 'one.jpg', 'two.jpg');

    commitBrandMedia($this->brand, [
        imageRow($two, ['alt' => 'Second', 'focal' => ['x' => 30, 'y' => 60]]),
        imageRow($one, ['alt' => 'First']),
    ])->assertOk();

    expect(brandImages($this->brand))->toBe([
        ['two.jpg', 'Second', true],
        ['one.jpg', 'First', false],
    ])
        ->and($this->brand->getFirstMedia($this->images)->getCustomProperty('focal'))->toBe(['x' => 30, 'y' => 60])
        ->and(EditDraft::count())->toBe(0)
        ->and(Media::query()->where('model_type', (new EditDraft)->getMorphClass())->count())->toBe(0);
});

it('reorders, edits and deletes the record media only on commit', function () {
    [$a, $b, $c] = collect(['a.jpg', 'b.jpg', 'c.jpg'])
        ->map(fn ($name) => $this->brand->addMedia(UploadedFile::fake()->image($name))->toMediaCollection($this->images)->id)
        ->all();

    $rows = [imageRow($c, ['alt' => 'Hero now']), imageRow($a)];

    $this->patchJson(route('panel.brands.draft.update', $this->brand), ['data' => ['media:'.$this->images => $rows]])
        ->assertOk();

    expect(array_column(brandImages($this->brand), 0))->toBe(['a.jpg', 'b.jpg', 'c.jpg']);

    commitBrandMedia($this->brand, $rows)->assertOk();

    expect(brandImages($this->brand))->toBe([
        ['c.jpg', 'Hero now', true],
        ['a.jpg', null, false],
    ])->and(Media::find($b))->toBeNull();
});

it('deletes uploads removed before Save', function () {
    [$kept, $dropped] = stageImages($this->brand, 'kept.jpg', 'dropped.jpg');

    commitBrandMedia($this->brand, [imageRow($kept)])->assertOk();

    expect(array_column(brandImages($this->brand), 0))->toBe(['kept.jpg'])
        ->and(Media::find($dropped))->toBeNull();
});

it('deletes staged uploads when the draft is discarded', function () {
    [$id] = stageImages($this->brand, 'one.jpg');

    $this->deleteJson(route('panel.brands.draft.destroy', $this->brand))->assertSuccessful();

    expect(Media::find($id))->toBeNull()
        ->and($this->brand->refresh()->getMedia($this->images))->toHaveCount(0);
});

it('sweeps staged uploads no draft lists once the grace period passes', function () {
    [$listed, $unlisted] = stageImages($this->brand, 'listed.jpg', 'unlisted.jpg');

    $this->patchJson(route('panel.brands.draft.update', $this->brand), [
        'data' => ['media:'.$this->images => [imageRow($listed)]],
    ])->assertOk();

    // A staged upload whose draft is gone (a commit that failed after its transaction).
    $orphanDraft = EditDraft::factory()->create();
    $orphan = $orphanDraft->addMedia(UploadedFile::fake()->image('orphan.jpg'))->toMediaCollection($this->images);
    $orphanDraft->deleteQuietly();

    // A fresh unlisted upload is spared: its list may not have autosaved yet.
    $fresh = stageImages($this->brand, 'fresh.jpg')[0];

    Media::query()->whereKey([$listed, $unlisted, $orphan->id])->update(['created_at' => now()->subHours(2)]);

    (new EditDraft)->pruneAll();

    expect(Media::query()->whereKey([$listed, $unlisted, $orphan->id, $fresh])->pluck('id')->sort()->values()->all())
        ->toBe(collect([$listed, $fresh])->sort()->values()->all());
});

it('refuses ids that are neither the record media nor its staged uploads', function () {
    $other = Brand::factory()->create();
    $foreign = $other->addMedia(UploadedFile::fake()->image('foreign.jpg'))->toMediaCollection($this->images);

    commitBrandMedia($this->brand, [imageRow($foreign->id)])
        ->assertJsonValidationErrors('media:'.$this->images.'.0.id');

    expect($other->refresh()->getMedia($this->images))->toHaveCount(1);
});

it('conflicts on the collection a colleague changed meanwhile', function () {
    $a = $this->brand->addMedia(UploadedFile::fake()->image('a.jpg'))->toMediaCollection($this->images);
    $b = $this->brand->addMedia(UploadedFile::fake()->image('b.jpg'))->toMediaCollection($this->images);

    $mine = ['media:'.$this->images => [imageRow($b->id), imageRow($a->id)]];

    $this->patchJson(route('panel.brands.draft.update', $this->brand), ['data' => $mine])->assertOk();

    // A colleague sets alt text meanwhile.
    $a->setCustomProperty('alt', 'Theirs')->save();

    $response = $this->postJson(route('panel.brands.draft.commit', $this->brand), ['data' => $mine, 'rebase' => []])
        ->assertConflict();

    expect(collect($response->json('conflicts'))->pluck('key')->all())->toBe(['media:'.$this->images])
        ->and(array_column(brandImages($this->brand), 0))->toBe(['a.jpg', 'b.jpg']);
});

it('names a removed image in the error rather than its raw key', function () {
    $a = $this->brand->addMedia(UploadedFile::fake()->image('a.jpg'))->toMediaCollection($this->images);

    $a->delete();

    commitBrandMedia($this->brand, [imageRow($a->id)])
        ->assertJsonValidationErrors(['media:'.$this->images.'.0.id' => 'The selected Media is invalid.']);
});

it('stages and commits media on every drafted media page', function (string $model, string $prefix) {
    $record = match ($model) {
        Collection::class => Collection::factory()->create(['collection_group_id' => CollectionGroup::factory()->create()->id]),
        default => $model::factory()->create(),
    };

    $id = $this->postJson(route("panel.{$prefix}.media.store", $record), [
        'files' => [UploadedFile::fake()->image('hero.jpg', 400, 400)],
    ])->assertOk()->json('items.0.id');

    $this->postJson(route("panel.{$prefix}.draft.commit", $record), [
        'data' => ['media:'.$this->images => [imageRow($id, ['alt' => 'Hero'])]],
        'rebase' => [],
    ])->assertOk();

    $media = $record->refresh()->getFirstMedia($this->images);

    expect($media?->getCustomProperty('alt'))->toBe('Hero')
        ->and((bool) $media?->getCustomProperty('primary'))->toBeTrue();
})->with([
    'product' => [Product::class, 'products'],
    'brand' => [Brand::class, 'brands'],
    'collection' => [Collection::class, 'collections'],
    'product type' => [ProductType::class, 'product-types'],
]);
