<?php

use Inertia\Testing\AssertableInertia as Assert;
use Lunar\Core\Models\Brand;
use Lunar\Core\Models\Collection;
use Lunar\Core\Models\CollectionGroup;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Staff;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

function makeCollection(string $name): Collection
{
    return Collection::factory()->create([
        'collection_group_id' => CollectionGroup::factory(),
        'name' => ['en' => $name],
    ]);
}

beforeEach(function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    Language::factory()->create(['default' => true, 'code' => 'en']);

    $this->brand = Brand::factory()->create();
});

it('searches collections by name', function () {
    makeCollection('Flagship Phones');
    makeCollection('Laptops');

    $this->getJson(route('panel.catalog.collections.search', ['q' => 'flagship']))
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'Flagship Phones');

    $this->getJson(route('panel.catalog.collections.search'))
        ->assertOk()
        ->assertJsonCount(2, 'data');
});

it('gates the collection search behind the catalog permission', function () {
    $this->actingAs(Staff::factory()->create(['admin' => false]), 'staff');

    $this->getJson(route('panel.catalog.collections.search'))->assertForbidden();
});

it('serves selected collections on the edit page', function () {
    $collection = makeCollection('Flagship Phones');
    $this->brand->collections()->attach($collection);

    $this->get(route('panel.brands.edit', $this->brand))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('collections', 1)
            ->where('collections.0.name', 'Flagship Phones')
            ->has('urls.collectionsSearch')
        );
});

it('drafts and commits collection membership', function () {
    $keep = makeCollection('Keep');
    $add = makeCollection('Add');
    $drop = makeCollection('Drop');

    $this->brand->collections()->sync([$keep->id, $drop->id]);

    $this->patchJson(route('panel.brands.draft.update', $this->brand), [
        'data' => ['collection_ids' => [$add->id, $keep->id]],
    ])->assertOk();

    $this->postJson(route('panel.brands.draft.commit', $this->brand), [
        'data' => [],
        'rebase' => [],
    ])->assertOk();

    expect($this->brand->collections()->pluck('collection_id')->sort()->values()->all())
        ->toBe(collect([$keep->id, $add->id])->sort()->values()->all());
});

it('rejects unknown collection ids at commit', function () {
    $this->postJson(route('panel.brands.draft.commit', $this->brand), [
        'data' => ['collection_ids' => [999999]],
        'rebase' => [],
    ])->assertUnprocessable();
});

it('detects conflicts on collection membership', function () {
    $mine = makeCollection('Mine');
    $theirs = makeCollection('Theirs');

    $this->patchJson(route('panel.brands.draft.update', $this->brand), [
        'data' => ['collection_ids' => [$mine->id]],
    ])->assertOk();

    $this->brand->collections()->sync([$theirs->id]);

    $this->postJson(route('panel.brands.draft.commit', $this->brand), [
        'data' => [],
        'rebase' => [],
    ])->assertConflict()
        ->assertJsonPath('conflicts.0.key', 'collection_ids');
});

it('syncs collections through the update endpoint', function () {
    $collection = makeCollection('Flagship Phones');

    $this->put(route('panel.brands.update', $this->brand), [
        'name' => $this->brand->name,
        'handle' => $this->brand->handle,
        'collection_ids' => [$collection->id],
    ])->assertRedirect();

    expect($this->brand->collections()->pluck('collection_id')->all())->toBe([$collection->id]);
});
