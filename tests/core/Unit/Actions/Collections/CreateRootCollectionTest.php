<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Actions\Collections\CreateChildCollection;
use Lunar\Core\Actions\Collections\CreateRootCollection;
use Lunar\Core\Models\Collection;
use Lunar\Core\Models\CollectionGroup;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Language;
use Lunar\Core\States\Collection\Published;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

beforeEach(function () {
    Language::factory()->create(['default' => true, 'code' => 'en']);
    Currency::factory()->create(['default' => true, 'decimal_places' => 2]);
});

test('creates a root collection inside a group', function () {
    $group = CollectionGroup::factory()->create();

    $collection = app(CreateRootCollection::class)->execute($group->id, 'Trousers');

    expect($collection)->toBeInstanceOf(Collection::class);
    expect($collection->collection_group_id)->toBe($group->id);
    expect($collection->isRoot())->toBeTrue();
});

test('creates a child collection under a parent', function () {
    $group = CollectionGroup::factory()->create();
    $parent = app(CreateRootCollection::class)->execute($group->id, 'Outerwear');

    $child = app(CreateChildCollection::class)->execute($parent->fresh(), 'Coats');

    expect($child->parent_id)->toBe($parent->id);
    expect($child->collection_group_id)->toBe($group->id);
});

test('applies further attributes when given', function () {
    $group = CollectionGroup::factory()->create();

    $collection = app(CreateRootCollection::class)->execute($group->id, 'Trousers', [
        'handle' => 'legwear',
        'status' => 'published',
        'short_description' => ['en' => 'All the trousers.'],
    ]);

    expect($collection->handle)->toBe('legwear');
    expect($collection->status)->toBeInstanceOf(Published::class);
    expect($collection->translate('short_description'))->toBe('All the trousers.');
});

test('generates a handle from the name when none is given', function () {
    $group = CollectionGroup::factory()->create();

    $collection = app(CreateRootCollection::class)->execute($group->id, 'Summer Sale');

    expect($collection->handle)->toBe('summer-sale');
});

test('suffixes a generated handle until unique', function () {
    $group = CollectionGroup::factory()->create();
    Collection::factory()->create(['handle' => 'summer-sale']);
    Collection::factory()->create(['handle' => 'summer-sale-2']);

    $collection = app(CreateRootCollection::class)->execute($group->id, 'Summer Sale');

    expect($collection->handle)->toBe('summer-sale-3');
});

test('a child collection generates its handle too', function () {
    $group = CollectionGroup::factory()->create();
    $parent = app(CreateRootCollection::class)->execute($group->id, 'Outerwear');

    $child = app(CreateChildCollection::class)->execute($parent->fresh(), 'Coats', [
        'status' => 'published',
    ]);

    expect($child->handle)->toBe('coats');
    expect($child->status)->toBeInstanceOf(Published::class);
});
