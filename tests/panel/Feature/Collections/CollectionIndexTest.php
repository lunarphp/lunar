<?php

use Inertia\Testing\AssertableInertia as Assert;
use Lunar\Core\Models\Collection;
use Lunar\Core\Models\CollectionGroup;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Staff;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

beforeEach(function () {
    // Collection creation triggers the HasUrls generator, which needs a default language.
    Language::factory()->create(['default' => true]);
});

it('redirects guests to the login screen', function () {
    $this->get(route('panel.collections.index'))->assertRedirect(route('panel.login'));
});

it('renders only root collections, deferring children to lazy loading', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    $group = CollectionGroup::factory()->create(['name' => 'Navigation']);
    $root = Collection::factory()->create(['collection_group_id' => $group->id]);
    $child = Collection::factory()->create(['collection_group_id' => $group->id]);
    $root->appendNode($child);

    $this->get(route('panel.collections.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('collections/Index')
            ->has('groups', 1)
            ->where('groups.0.name', 'Navigation')
            ->where('groups.0.collections_count', 2)
            // Only the root ships; its child is fetched on expand.
            ->has('groups.0.tree', 1)
            ->has('groups.0.tree.0', fn (Assert $row) => $row
                ->hasAll(['id', 'name', 'handle', 'thumbnail', 'short_description', 'status', 'status_label', 'products_count', 'descendants_count', 'matched', 'edit_url', 'children_url', '_actions', 'children'])
                ->where('id', $root->id)
                ->where('descendants_count', 1)
                ->has('children', 0)
                ->etc()
            )
            ->has('tableActions')
            ->has('urls.create')
        );
});

it('lazy-loads a collection\'s immediate children', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    $group = CollectionGroup::factory()->create();
    $root = Collection::factory()->create(['collection_group_id' => $group->id]);
    $child = Collection::factory()->create(['collection_group_id' => $group->id]);
    $grandchild = Collection::factory()->create(['collection_group_id' => $group->id]);
    $root->appendNode($child);
    $child->refresh()->appendNode($grandchild);

    $this->getJson(route('panel.collections.children', $root))
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $child->id)
        // The grandchild is a descendant but not an immediate child.
        ->assertJsonPath('data.0.descendants_count', 1)
        ->assertJsonPath('data.0.children', []);
});

it('gates the children endpoint behind the collections permission', function () {
    $this->actingAs(Staff::factory()->create(['admin' => false]), 'staff');

    $collection = Collection::factory()->create();

    $this->getJson(route('panel.collections.children', $collection))->assertForbidden();
});

it('searches by name and includes ancestors for context', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    $group = CollectionGroup::factory()->create();
    $root = Collection::factory()->create([
        'collection_group_id' => $group->id,
        'name' => ['en' => 'Outerwear'],
    ]);
    $child = Collection::factory()->create([
        'collection_group_id' => $group->id,
        'name' => ['en' => 'Raincoats'],
    ]);
    $root->appendNode($child);
    Collection::factory()->create([
        'collection_group_id' => $group->id,
        'name' => ['en' => 'Footwear'],
    ]);

    $this->get(route('panel.collections.index', ['q' => 'Raincoats']))
        ->assertInertia(fn (Assert $page) => $page
            ->where('filtering', true)
            ->where('matchedCount', 1)
            ->has('groups.0.tree', 1)
            // The ancestor rides along for context but is not a match itself.
            ->where('groups.0.tree.0.id', $root->id)
            ->where('groups.0.tree.0.matched', false)
            ->has('groups.0.tree.0.children', 1)
            ->where('groups.0.tree.0.children.0.matched', true)
        );
});

it('searches by handle and url slug', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    $group = CollectionGroup::factory()->create();
    $collection = Collection::factory()->create([
        'collection_group_id' => $group->id,
        'name' => ['en' => 'Outerwear'],
        'handle' => 'outer-things',
    ]);
    $collection->urls()->create([
        'slug' => 'super-slug',
        'default' => true,
        'language_id' => Language::first()->id,
    ]);
    Collection::factory()->create(['collection_group_id' => $group->id]);

    $this->get(route('panel.collections.index', ['q' => 'outer-things']))
        ->assertInertia(fn (Assert $page) => $page->where('matchedCount', 1));

    $this->get(route('panel.collections.index', ['q' => 'super-slug']))
        ->assertInertia(fn (Assert $page) => $page->where('matchedCount', 1));
});

it('filters by status', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    $group = CollectionGroup::factory()->create();
    Collection::factory()->published()->count(2)->create(['collection_group_id' => $group->id]);
    Collection::factory()->draft()->create(['collection_group_id' => $group->id]);

    $this->get(route('panel.collections.index', ['status' => 'draft']))
        ->assertInertia(fn (Assert $page) => $page->where('matchedCount', 1));

    $this->get(route('panel.collections.index', ['status' => 'nonsense']))
        ->assertInertia(fn (Assert $page) => $page
            ->where('filtering', false)
            ->where('matchedCount', 3)
        );
});

it('keeps empty groups visible', function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    CollectionGroup::factory()->create();

    $this->get(route('panel.collections.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->has('groups', 1)
            ->where('groups.0.collections_count', 0)
            ->has('groups.0.tree', 0)
        );
});

it('gates the index behind the collections permission', function () {
    $this->actingAs(Staff::factory()->create(['admin' => false]), 'staff');

    $this->get(route('panel.collections.index'))->assertForbidden();
});
