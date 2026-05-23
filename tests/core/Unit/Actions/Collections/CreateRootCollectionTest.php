<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Actions\Collections\CreateChildCollection;
use Lunar\Core\Actions\Collections\CreateRootCollection;
use Lunar\Core\FieldTypes\Text;
use Lunar\Core\Models\Attribute;
use Lunar\Core\Models\Collection;
use Lunar\Core\Models\CollectionGroup;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Language;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

beforeEach(function () {
    Language::factory()->create(['default' => true, 'code' => 'en']);
    Currency::factory()->create(['default' => true, 'decimal_places' => 2]);

    Attribute::factory()->create([
        'handle' => 'name',
        'attribute_type' => Collection::morphName(),
        'type' => Text::class,
    ]);
});

test('creates a root collection inside a group', function () {
    $group = CollectionGroup::factory()->create();

    $collection = CreateRootCollection::run($group->id, 'Trousers');

    expect($collection)->toBeInstanceOf(Collection::class);
    expect($collection->collection_group_id)->toBe($group->id);
    expect($collection->isRoot())->toBeTrue();
});

test('creates a child collection under a parent', function () {
    $group = CollectionGroup::factory()->create();
    $parent = CreateRootCollection::run($group->id, 'Outerwear');

    $child = CreateChildCollection::run($parent->fresh(), 'Coats');

    expect($child->parent_id)->toBe($parent->id);
    expect($child->collection_group_id)->toBe($group->id);
});
