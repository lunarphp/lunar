<?php

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

it('re-parents within the group', function () {
    $a = Collection::factory()->create(['collection_group_id' => $this->group->id]);
    $b = Collection::factory()->create(['collection_group_id' => $this->group->id]);

    $this->put(route('panel.collections.move', $a), [
        'collection_group_id' => $this->group->id,
        'parent_id' => $b->id,
    ])->assertRedirect();

    expect($a->fresh()->parent_id)->toBe($b->id);
});

it('moves into another group at root level and resets the parent', function () {
    $other = CollectionGroup::factory()->create();

    $parent = Collection::factory()->create(['collection_group_id' => $this->group->id]);
    $child = Collection::factory()->create(['collection_group_id' => $this->group->id]);
    $parent->appendNode($child);

    $this->put(route('panel.collections.move', $child->fresh()), [
        'collection_group_id' => $other->id,
        'parent_id' => null,
    ]);

    $child->refresh();

    expect($child->collection_group_id)->toBe($other->id)
        ->and($child->parent_id)->toBeNull();
});

it('rejects a cycle with an error flash', function () {
    $parent = Collection::factory()->create(['collection_group_id' => $this->group->id]);
    $child = Collection::factory()->create(['collection_group_id' => $this->group->id]);
    $parent->appendNode($child);

    $this->from(route('panel.collections.edit', $parent))
        ->put(route('panel.collections.move', $parent->fresh()), [
            'collection_group_id' => $this->group->id,
            'parent_id' => $child->id,
        ])
        ->assertRedirect(route('panel.collections.edit', $parent))
        ->assertSessionHas('error');

    expect($parent->fresh()->parent_id)->toBeNull();
});

it('rejects a parent outside the destination group', function () {
    $node = Collection::factory()->create(['collection_group_id' => $this->group->id]);
    $other = CollectionGroup::factory()->create();
    $target = Collection::factory()->create(['collection_group_id' => $this->group->id]);

    $this->put(route('panel.collections.move', $node), [
        'collection_group_id' => $other->id,
        'parent_id' => $target->id,
    ])->assertSessionHas('error');

    expect($node->fresh()->collection_group_id)->toBe($this->group->id);
});

it('gates the move behind the collections permission', function () {
    $node = Collection::factory()->create(['collection_group_id' => $this->group->id]);

    $this->actingAs(Staff::factory()->create(['admin' => false]), 'staff');

    $this->put(route('panel.collections.move', $node), [
        'collection_group_id' => $this->group->id,
    ])->assertForbidden();
});
