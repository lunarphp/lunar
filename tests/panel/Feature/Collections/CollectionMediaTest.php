<?php

use Illuminate\Http\UploadedFile;
use Lunar\Core\Models\Collection;
use Lunar\Core\Models\CollectionGroup;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Staff;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

/**
 * Smoke coverage: the controllers delegate to the same media actions the
 * brand endpoints proved out (spec 0052); these tests pin the collection
 * routes, bindings and permission gating.
 */
beforeEach(function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    Language::factory()->create(['default' => true]);

    $this->collection = Collection::factory()->create([
        'collection_group_id' => CollectionGroup::factory(),
    ]);
    $this->mediaCollection = config('lunar.media.collection');
});

it('uploads images to the media collection', function () {
    $this->post(route('panel.collections.media.store', $this->collection), [
        'files' => [
            UploadedFile::fake()->image('one.jpg'),
            UploadedFile::fake()->image('two.jpg'),
        ],
    ])->assertRedirect()->assertSessionHas('success');

    $media = $this->collection->refresh()->getMedia($this->mediaCollection)->values();

    expect($media)->toHaveCount(2)
        ->and($media[0]->getCustomProperty('primary'))->toBeTrue();
});

it('updates media custom properties including the focal point', function () {
    $media = $this->collection->addMedia(UploadedFile::fake()->image('one.jpg'))->toMediaCollection($this->mediaCollection);

    $this->put(route('panel.collections.media.update', [$this->collection, $media]), [
        'name' => 'Hero shot',
        'alt' => 'Collection hero',
        'focal' => ['x' => 20, 'y' => 80],
    ])->assertRedirect()->assertSessionHas('success');

    $media->refresh();

    expect($media->getCustomProperty('alt'))->toBe('Collection hero')
        ->and($media->getCustomProperty('focal'))->toBe(['x' => 20, 'y' => 80]);
});

it('reorders media, promoting the new first item to primary', function () {
    $first = $this->collection->addMedia(UploadedFile::fake()->image('one.jpg'))->toMediaCollection($this->mediaCollection);
    $second = $this->collection->addMedia(UploadedFile::fake()->image('two.jpg'))->toMediaCollection($this->mediaCollection);

    $this->post(route('panel.collections.media.reorder', $this->collection), [
        'ids' => [$second->id, $first->id],
    ])->assertRedirect();

    $media = $this->collection->refresh()->getMedia($this->mediaCollection)->values();

    expect($media[0]->id)->toBe($second->id)
        ->and($media[0]->getCustomProperty('primary'))->toBeTrue();
});

it('deletes media', function () {
    $media = $this->collection->addMedia(UploadedFile::fake()->image('one.jpg'))->toMediaCollection($this->mediaCollection);

    $this->delete(route('panel.collections.media.destroy', [$this->collection, $media]))
        ->assertRedirect()->assertSessionHas('success');

    expect($this->collection->refresh()->getMedia($this->mediaCollection))->toHaveCount(0);
});

it('scopes media to the parent collection', function () {
    $other = Collection::factory()->create(['collection_group_id' => CollectionGroup::factory()]);
    $media = $other->addMedia(UploadedFile::fake()->image('one.jpg'))->toMediaCollection($this->mediaCollection);

    $this->delete(route('panel.collections.media.destroy', [$this->collection, $media]))
        ->assertNotFound();
});

it('gates media endpoints behind the collections permission', function () {
    $this->actingAs(Staff::factory()->create(['admin' => false]), 'staff');

    $this->post(route('panel.collections.media.store', $this->collection), [
        'files' => [UploadedFile::fake()->image('one.jpg')],
    ])->assertForbidden();
});
