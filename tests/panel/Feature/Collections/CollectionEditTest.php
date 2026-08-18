<?php

use Inertia\Testing\AssertableInertia as Assert;
use Lunar\Core\Models\Collection;
use Lunar\Core\Models\CollectionGroup;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Staff;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    Language::factory()->create(['default' => true, 'code' => 'en']);

    $this->group = CollectionGroup::factory()->create();
});

it('renders the edit page with the collection payload', function () {
    $root = Collection::factory()->create(['collection_group_id' => $this->group->id]);
    $collection = Collection::factory()->create([
        'collection_group_id' => $this->group->id,
        'name' => ['en' => 'Raincoats'],
        'handle' => 'raincoats',
    ]);
    $root->appendNode($collection);

    $this->get(route('panel.collections.edit', $collection))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('collections/Edit')
            ->where('collection.id', $collection->id)
            ->where('collection.display_name', 'Raincoats')
            ->where('collection.handle', 'raincoats')
            ->where('collection.group_id', $this->group->id)
            ->where('collection.parent.id', $root->id)
            ->has('groups', 1)
            ->has('languages', 1)
            ->has('attributeGroups')
            ->has('urls.move')
            ->has('urls.draft')
        );
});

it('updates through the update endpoint', function () {
    $collection = Collection::factory()->create([
        'collection_group_id' => $this->group->id,
        'handle' => 'old-handle',
    ]);

    $this->put(route('panel.collections.update', $collection), [
        'name' => ['en' => 'Renamed'],
        'handle' => 'renamed',
        'status' => 'published',
        'sort' => 'sku:asc',
    ])->assertRedirect();

    $collection->refresh();

    expect($collection->translate('name'))->toBe('Renamed')
        ->and($collection->handle)->toBe('renamed')
        ->and($collection->status->getValue())->toBe('published')
        ->and($collection->sort)->toBe('sku:asc');
});

it('rejects an update whose name map is entirely blank', function () {
    $collection = Collection::factory()->create(['collection_group_id' => $this->group->id]);

    $this->put(route('panel.collections.update', $collection), [
        'name' => ['en' => ''],
        'handle' => 'whatever',
    ])->assertSessionHasErrors('name');
});

it('deletes a leaf collection', function () {
    $collection = Collection::factory()->create(['collection_group_id' => $this->group->id]);

    $this->delete(route('panel.collections.destroy', $collection))
        ->assertRedirect(route('panel.collections.index'));

    $this->assertDatabaseMissing('lunar_collections', ['id' => $collection->id]);
});

it('refuses to delete a collection with children without the reparent flag', function () {
    $parent = Collection::factory()->create(['collection_group_id' => $this->group->id]);
    $child = Collection::factory()->create(['collection_group_id' => $this->group->id]);
    $parent->appendNode($child);

    $this->from(route('panel.collections.edit', $parent))
        ->delete(route('panel.collections.destroy', $parent))
        ->assertRedirect(route('panel.collections.edit', $parent));

    $this->assertDatabaseHas('lunar_collections', ['id' => $parent->id]);
});

it('promotes children when deleting with the reparent flag', function () {
    $grandparent = Collection::factory()->create(['collection_group_id' => $this->group->id]);
    $parent = Collection::factory()->create(['collection_group_id' => $this->group->id]);
    $child = Collection::factory()->create(['collection_group_id' => $this->group->id]);
    $grandparent->appendNode($parent);
    $parent->refresh()->appendNode($child);

    $this->delete(route('panel.collections.destroy', [$parent->fresh(), 'reparent' => 1]));

    $this->assertDatabaseMissing('lunar_collections', ['id' => $parent->id]);

    expect($child->fresh()->parent_id)->toBe($grandparent->id);
});

it('promotes children of a root to root level', function () {
    $parent = Collection::factory()->create(['collection_group_id' => $this->group->id]);
    $child = Collection::factory()->create(['collection_group_id' => $this->group->id]);
    $parent->appendNode($child);

    $this->delete(route('panel.collections.destroy', [$parent->fresh(), 'reparent' => 1]));

    $this->assertDatabaseMissing('lunar_collections', ['id' => $parent->id]);

    expect($child->fresh()->parent_id)->toBeNull();
});

it('promotes several children (and their subtrees) without breaking the tree', function () {
    $parent = Collection::factory()->create(['collection_group_id' => $this->group->id]);
    $children = Collection::factory()->count(3)->create(['collection_group_id' => $this->group->id]);
    $grandchild = Collection::factory()->create(['collection_group_id' => $this->group->id]);

    foreach ($children as $child) {
        $parent->refresh()->appendNode($child);
    }

    $children[1]->refresh()->appendNode($grandchild);

    $this->delete(route('panel.collections.destroy', [$parent->fresh(), 'reparent' => 1]));

    $this->assertDatabaseMissing('lunar_collections', ['id' => $parent->id]);

    foreach ($children as $child) {
        expect($child->fresh()->parent_id)->toBeNull();
    }

    expect($grandchild->fresh()->parent_id)->toBe($children[1]->id)
        ->and(Collection::scoped(['collection_group_id' => $this->group->id])->isBroken())->toBeFalse();
});

it('gates collection routes behind the collections permission', function () {
    $collection = Collection::factory()->create(['collection_group_id' => $this->group->id]);

    $this->actingAs(Staff::factory()->create(['admin' => false]), 'staff');

    $this->get(route('panel.collections.edit', $collection))->assertForbidden();
    $this->delete(route('panel.collections.destroy', $collection))->assertForbidden();
});
