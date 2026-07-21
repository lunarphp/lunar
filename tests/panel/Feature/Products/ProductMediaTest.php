<?php

use Illuminate\Http\UploadedFile;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\Staff;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

/*
 * Smoke coverage only: the endpoints reuse the brand-proven media actions and
 * controller shape; the full behaviour matrix lives in the brand media tests.
 */

beforeEach(function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    Language::factory()->create(['default' => true, 'code' => 'en']);

    $this->product = Product::factory()->create();
});

it('uploads media to the product collection', function () {
    $this->post(route('panel.products.media.store', $this->product), [
        'files' => [UploadedFile::fake()->image('hero.jpg', 600, 600)],
    ])->assertRedirect();

    expect($this->product->getMedia(config('lunar.media.collection')))->toHaveCount(1);
});

it('updates custom properties and deletes media', function () {
    $this->post(route('panel.products.media.store', $this->product), [
        'files' => [UploadedFile::fake()->image('hero.jpg', 600, 600)],
    ]);

    $media = $this->product->getFirstMedia(config('lunar.media.collection'));

    $this->put(route('panel.products.media.update', [$this->product, $media]), [
        'alt' => 'A widget on a desk',
        'focal' => ['x' => 30, 'y' => 60],
    ])->assertRedirect();

    $media->refresh();

    expect($media->getCustomProperty('alt'))->toBe('A widget on a desk')
        ->and($media->getCustomProperty('focal'))->toBe(['x' => 30, 'y' => 60]);

    $this->delete(route('panel.products.media.destroy', [$this->product, $media]))->assertRedirect();

    expect($this->product->refresh()->getMedia(config('lunar.media.collection')))->toHaveCount(0);
});

it('scopes media routes to the owning product', function () {
    $other = Product::factory()->create();

    $this->post(route('panel.products.media.store', $other), [
        'files' => [UploadedFile::fake()->image('hero.jpg', 600, 600)],
    ]);

    $media = $other->getFirstMedia(config('lunar.media.collection'));

    $this->delete(route('panel.products.media.destroy', [$this->product, $media]))
        ->assertNotFound();
});
