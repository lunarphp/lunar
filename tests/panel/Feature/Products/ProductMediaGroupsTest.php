<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Inertia\Testing\AssertableInertia;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\Staff;
use Lunar\Tests\Core\Stubs\TestMediaGroupDefinitions;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

/*
 * Covers additional media groups (spec 0060): a model whose MediaDefinitions
 * registers a non-image "downloads" collection alongside the images collection.
 */

function fakePdf(string $name = 'spec.pdf'): UploadedFile
{
    return UploadedFile::fake()->createWithContent($name, "%PDF-1.4\n1 0 obj<<>>endobj\ntrailer<<>>\n%%EOF");
}

beforeEach(function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    Language::factory()->create(['default' => true, 'code' => 'en']);

    Config::set('lunar.media.definitions', [
        'product' => TestMediaGroupDefinitions::class,
    ]);

    $this->product = Product::factory()->create();
});

it('serves an image group and a file group on the edit page', function () {
    $this->product->addMedia(UploadedFile::fake()->image('hero.jpg'))->toMediaCollection('images');
    $this->product->addMedia(fakePdf())->toMediaCollection('downloads');

    $this->get(route('panel.products.edit', $this->product))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('mediaGroups', 2)
            ->where('mediaGroups.0.collection', 'images')
            ->where('mediaGroups.0.type', 'image')
            ->where('mediaGroups.1.collection', 'downloads')
            ->where('mediaGroups.1.type', 'file')
            ->where('mediaGroups.1.accept', 'application/pdf')
            ->hasAll([
                'mediaGroups.1.items.0.file_name',
                'mediaGroups.1.items.0.size',
                'mediaGroups.1.items.0.original_url',
                'mediaGroups.1.urls.store',
            ])
        );
});

it('uploads a pdf to the downloads group', function () {
    $this->post(route('panel.products.media.store', $this->product), [
        'collection' => 'downloads',
        'files' => [fakePdf()],
    ])->assertRedirect()->assertSessionHas('success');

    expect($this->product->getMedia('downloads'))->toHaveCount(1)
        ->and($this->product->getMedia('images'))->toHaveCount(0);
});

it('rejects a pdf uploaded to the images group', function () {
    $this->post(route('panel.products.media.store', $this->product), [
        'collection' => 'images',
        'files' => [fakePdf()],
    ])->assertSessionHasErrors('files.0');
});

it('rejects an image uploaded to the downloads group', function () {
    $this->post(route('panel.products.media.store', $this->product), [
        'collection' => 'downloads',
        'files' => [UploadedFile::fake()->image('hero.jpg')],
    ])->assertSessionHasErrors('files.0');
});

it('rejects a collection the model does not register', function () {
    $this->post(route('panel.products.media.store', $this->product), [
        'collection' => 'brochures',
        'files' => [fakePdf()],
    ])->assertSessionHasErrors('collection');
});

it('reorders the downloads group without touching the thumbnail', function () {
    $first = $this->product->addMedia(fakePdf('a.pdf'))->toMediaCollection('downloads');
    $second = $this->product->addMedia(fakePdf('b.pdf'))->toMediaCollection('downloads');

    $this->post(route('panel.products.media.reorder', $this->product), [
        'collection' => 'downloads',
        'ids' => [$second->id, $first->id],
    ])->assertRedirect();

    $ordered = $this->product->refresh()->getMedia('downloads');

    expect($ordered->pluck('id')->all())->toBe([$second->id, $first->id])
        ->and($ordered->every(fn ($item) => $item->getCustomProperty('primary') !== true))->toBeTrue()
        ->and($this->product->thumbnail)->toBeNull();
});
