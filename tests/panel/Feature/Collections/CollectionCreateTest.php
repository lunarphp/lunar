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

it('renders the create page with groups and preselection', function () {
    $parent = Collection::factory()->create(['collection_group_id' => $this->group->id]);

    $this->get(route('panel.collections.create', ['parent' => $parent->id]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('collections/Create')
            ->has('groups', 1)
            ->where('preselected.group_id', $this->group->id)
            ->where('preselected.parent.id', $parent->id)
        );
});

it('creates a root collection and redirects to edit', function () {
    $response = $this->post(route('panel.collections.store'), [
        'name' => 'Summer Sale',
        'collection_group_id' => $this->group->id,
        'status' => 'published',
    ]);

    $collection = Collection::sole();

    $response->assertRedirect(route('panel.collections.edit', $collection));

    expect($collection->translate('name'))->toBe('Summer Sale')
        ->and($collection->handle)->toBe('summer-sale')
        ->and($collection->status->getValue())->toBe('published')
        ->and($collection->isRoot())->toBeTrue();
});

it('creates a child under the chosen parent', function () {
    $parent = Collection::factory()->create(['collection_group_id' => $this->group->id]);

    $this->post(route('panel.collections.store'), [
        'name' => 'Coats',
        'collection_group_id' => $this->group->id,
        'parent_id' => $parent->id,
    ]);

    $child = Collection::query()->where('id', '!=', $parent->id)->sole();

    expect($child->parent_id)->toBe($parent->id)
        ->and($child->collection_group_id)->toBe($this->group->id)
        ->and($child->status->getValue())->toBe('draft');
});

it('rejects a parent from another group', function () {
    $otherGroup = CollectionGroup::factory()->create();
    $parent = Collection::factory()->create(['collection_group_id' => $otherGroup->id]);

    $this->post(route('panel.collections.store'), [
        'name' => 'Coats',
        'collection_group_id' => $this->group->id,
        'parent_id' => $parent->id,
    ])->assertSessionHasErrors('parent_id');
});

it('requires a name and group', function () {
    $this->post(route('panel.collections.store'), [])
        ->assertSessionHasErrors(['name', 'collection_group_id']);
});
