<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Actions\Collections\MoveCollection;
use Lunar\Core\Exceptions\CollectionActionException;
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

    $this->group = CollectionGroup::factory()->create();
});

test('re-parents a collection under a target', function () {
    $a = Collection::factory()->create(['collection_group_id' => $this->group->id]);
    $b = Collection::factory()->create(['collection_group_id' => $this->group->id]);

    app(MoveCollection::class)->execute($a, $b);

    expect($a->fresh()->parent_id)->toBe($b->id);
});

test('makes a collection a root when target is null', function () {
    $parent = Collection::factory()->create(['collection_group_id' => $this->group->id]);
    $child = Collection::factory()->create(['collection_group_id' => $this->group->id]);
    $parent->appendNode($child);

    app(MoveCollection::class)->execute($child->fresh(), null);

    expect($child->fresh()->parent_id)->toBeNull();
});

test('rejects moving a collection into itself', function () {
    $a = Collection::factory()->create(['collection_group_id' => $this->group->id]);

    app(MoveCollection::class)->execute($a, $a);
})->throws(CollectionActionException::class);

test('rejects moving a collection into one of its descendants', function () {
    $parent = Collection::factory()->create(['collection_group_id' => $this->group->id]);
    $child = Collection::factory()->create(['collection_group_id' => $this->group->id]);
    $parent->appendNode($child);

    app(MoveCollection::class)->execute($parent->fresh(), $child->fresh());
})->throws(CollectionActionException::class);

test('moves a collection and its subtree into another group', function () {
    $other = CollectionGroup::factory()->create();

    $parent = Collection::factory()->create(['collection_group_id' => $this->group->id]);
    $child = Collection::factory()->create(['collection_group_id' => $this->group->id]);
    $sibling = Collection::factory()->create(['collection_group_id' => $this->group->id]);
    $parent->appendNode($child);

    app(MoveCollection::class)->execute($parent->fresh(), null, $other);

    expect($parent->fresh()->collection_group_id)->toBe($other->id)
        ->and($parent->fresh()->parent_id)->toBeNull()
        ->and($child->fresh()->collection_group_id)->toBe($other->id)
        ->and($child->fresh()->parent_id)->toBe($parent->id)
        ->and($sibling->fresh()->collection_group_id)->toBe($this->group->id);

    // Both scopes hold consistent nested-set bounds after the rebuild.
    expect(Collection::scoped(['collection_group_id' => $this->group->id])->isBroken())->toBeFalse()
        ->and(Collection::scoped(['collection_group_id' => $other->id])->isBroken())->toBeFalse();
});

test('moves into another group under a target parent in that group', function () {
    $other = CollectionGroup::factory()->create();

    $node = Collection::factory()->create(['collection_group_id' => $this->group->id]);
    $target = Collection::factory()->create(['collection_group_id' => $other->id]);

    app(MoveCollection::class)->execute($node, $target->fresh(), $other);

    expect($node->fresh()->collection_group_id)->toBe($other->id)
        ->and($node->fresh()->parent_id)->toBe($target->id);
});

test('rejects a target parent outside the destination group', function () {
    $other = CollectionGroup::factory()->create();

    $node = Collection::factory()->create(['collection_group_id' => $this->group->id]);
    $target = Collection::factory()->create(['collection_group_id' => $this->group->id]);

    app(MoveCollection::class)->execute($node, $target->fresh(), $other);
})->throws(CollectionActionException::class);
