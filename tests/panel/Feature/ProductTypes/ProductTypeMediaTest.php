<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Lunar\Core\Models\ProductType;
use Lunar\Core\Models\Staff;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    $this->productType = ProductType::factory()->create();
    $this->collection = config('lunar.media.collection');
});

function productTypeMedia(ProductType $productType): Collection
{
    return $productType->refresh()->getMedia(config('lunar.media.collection'))->values();
}

it('uploads images to the media collection', function () {
    $this->post(route('panel.product-types.media.store', $this->productType), [
        'files' => [
            UploadedFile::fake()->image('one.jpg'),
            UploadedFile::fake()->image('two.jpg'),
        ],
    ])->assertRedirect()->assertSessionHas('success');

    $media = productTypeMedia($this->productType);

    expect($media)->toHaveCount(2)
        ->and($media[0]->getCustomProperty('primary'))->toBeTrue();
});

it('rejects non-image uploads', function () {
    $this->post(route('panel.product-types.media.store', $this->productType), [
        'files' => [UploadedFile::fake()->create('document.pdf', 100)],
    ])->assertSessionHasErrors('files.0');
});

it('updates media custom properties', function () {
    $media = $this->productType->addMedia(UploadedFile::fake()->image('one.jpg'))->toMediaCollection($this->collection);

    $this->put(route('panel.product-types.media.update', [$this->productType, $media]), [
        'name' => 'Hero',
        'alt' => 'A stationery flat lay',
        'focal' => ['x' => 30, 'y' => 60],
    ])->assertRedirect()->assertSessionHas('success');

    $media->refresh();

    expect($media->getCustomProperty('name'))->toBe('Hero')
        ->and($media->getCustomProperty('alt'))->toBe('A stationery flat lay')
        ->and($media->getCustomProperty('focal'))->toBe(['x' => 30, 'y' => 60]);
});

it('reorders media and deletes items', function () {
    $first = $this->productType->addMedia(UploadedFile::fake()->image('one.jpg'))->toMediaCollection($this->collection);
    $second = $this->productType->addMedia(UploadedFile::fake()->image('two.jpg'))->toMediaCollection($this->collection);

    $this->post(route('panel.product-types.media.reorder', $this->productType), [
        'ids' => [$second->id, $first->id],
    ])->assertRedirect();

    expect(productTypeMedia($this->productType)->first()->id)->toBe($second->id);

    $this->delete(route('panel.product-types.media.destroy', [$this->productType, $second]))
        ->assertRedirect()->assertSessionHas('success');

    expect(productTypeMedia($this->productType))->toHaveCount(1);
});

it('scopes media routes to the owning product type', function () {
    $other = ProductType::factory()->create();
    $media = $this->productType->addMedia(UploadedFile::fake()->image('one.jpg'))->toMediaCollection($this->collection);

    $this->delete(route('panel.product-types.media.destroy', [$other, $media]))
        ->assertNotFound();
});
