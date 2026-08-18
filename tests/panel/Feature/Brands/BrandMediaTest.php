<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Inertia\Testing\AssertableInertia;
use Lunar\Core\Models\Brand;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Staff;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    Language::factory()->create(['default' => true]);

    $this->brand = Brand::factory()->create();
    $this->collection = config('lunar.media.collection');
});

function brandMedia(Brand $brand): Collection
{
    return $brand->refresh()->getMedia(config('lunar.media.collection'))->values();
}

it('uploads images to the media collection', function () {
    $this->post(route('panel.brands.media.store', $this->brand), [
        'files' => [
            UploadedFile::fake()->image('one.jpg'),
            UploadedFile::fake()->image('two.jpg'),
        ],
    ])->assertRedirect()->assertSessionHas('success');

    $media = brandMedia($this->brand);

    expect($media)->toHaveCount(2)
        ->and($media[0]->getCustomProperty('primary'))->toBeTrue();
});

it('rejects non-image uploads', function () {
    $this->post(route('panel.brands.media.store', $this->brand), [
        'files' => [UploadedFile::fake()->create('document.pdf', 100)],
    ])->assertSessionHasErrors('files.0');
});

it('updates media custom properties including the focal point', function () {
    $media = $this->brand->addMedia(UploadedFile::fake()->image('one.jpg'))->toMediaCollection($this->collection);

    $this->put(route('panel.brands.media.update', [$this->brand, $media]), [
        'name' => 'Hero shot',
        'alt' => 'Front of the product',
        'caption' => 'Shot in the studio.',
        'focal' => ['x' => 20, 'y' => 80],
    ])->assertRedirect()->assertSessionHas('success');

    $media->refresh();

    expect($media->getCustomProperty('alt'))->toBe('Front of the product')
        ->and($media->getCustomProperty('caption'))->toBe('Shot in the studio.')
        ->and($media->getCustomProperty('focal'))->toBe(['x' => 20, 'y' => 80]);
});

it('requires alt text on update', function () {
    $media = $this->brand->addMedia(UploadedFile::fake()->image('one.jpg'))->toMediaCollection($this->collection);

    $this->put(route('panel.brands.media.update', [$this->brand, $media]), [
        'alt' => '',
    ])->assertSessionHasErrors('alt');
});

it('persists a reorder and promotes the new first item to primary', function () {
    $first = $this->brand->addMedia(UploadedFile::fake()->image('one.jpg'))->toMediaCollection($this->collection);
    $second = $this->brand->addMedia(UploadedFile::fake()->image('two.jpg'))->toMediaCollection($this->collection);

    $this->post(route('panel.brands.media.reorder', $this->brand), [
        'ids' => [$second->id, $first->id],
    ])->assertRedirect();

    $media = brandMedia($this->brand);

    expect($media->pluck('id')->all())->toBe([$second->id, $first->id])
        ->and($media[0]->getCustomProperty('primary'))->toBeTrue()
        ->and($media[1]->getCustomProperty('primary'))->toBeFalse();
});

it('rejects a reorder whose ids do not match the collection', function () {
    $this->brand->addMedia(UploadedFile::fake()->image('one.jpg'))->toMediaCollection($this->collection);

    $this->post(route('panel.brands.media.reorder', $this->brand), [
        'ids' => [999999],
    ])->assertSessionHasErrors('ids');
});

it('deletes media and re-points the primary flag', function () {
    $first = $this->brand->addMedia(UploadedFile::fake()->image('one.jpg'))->toMediaCollection($this->collection);
    $second = $this->brand->addMedia(UploadedFile::fake()->image('two.jpg'))->toMediaCollection($this->collection);

    $this->delete(route('panel.brands.media.destroy', [$this->brand, $first]))
        ->assertRedirect()->assertSessionHas('success');

    $media = brandMedia($this->brand);

    expect($media)->toHaveCount(1)
        ->and($media[0]->id)->toBe($second->id)
        ->and($media[0]->getCustomProperty('primary'))->toBeTrue();
});

it('scopes media bindings to the brand', function () {
    $other = Brand::factory()->create();
    $foreign = $other->addMedia(UploadedFile::fake()->image('one.jpg'))->toMediaCollection($this->collection);

    $this->delete(route('panel.brands.media.destroy', [$this->brand, $foreign]))
        ->assertNotFound();

    expect(brandMedia($other))->toHaveCount(1);
});

it('serves media props on the edit page', function () {
    $media = $this->brand->addMedia(UploadedFile::fake()->image('one.jpg'))->toMediaCollection($this->collection);
    $media->setCustomProperty('focal', ['x' => 10, 'y' => 90])->save();

    $this->get(route('panel.brands.edit', $this->brand))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('mediaGroups', 1)
            ->where('mediaGroups.0.type', 'image')
            ->where('mediaGroups.0.items.0.focal', ['x' => 10, 'y' => 90])
            ->hasAll([
                'mediaGroups.0.items.0.url',
                'mediaGroups.0.items.0.update_url',
                'mediaGroups.0.items.0.destroy_url',
                'mediaGroups.0.urls.store',
                'mediaGroups.0.urls.reorder',
            ])
        );
});
