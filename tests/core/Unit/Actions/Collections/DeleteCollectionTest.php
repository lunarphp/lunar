<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Actions\Collections\DeleteCollection;
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

test('deletes a leaf collection', function () {
    $collection = Collection::factory()->create(['collection_group_id' => $this->group->id]);

    expect(app(DeleteCollection::class)->execute($collection))->toBeTrue();
    expect(Collection::find($collection->id))->toBeNull();
});

test('rejects deleting a collection with descendants without a target', function () {
    $parent = Collection::factory()->create(['collection_group_id' => $this->group->id]);
    $child = Collection::factory()->create(['collection_group_id' => $this->group->id]);
    $parent->appendNode($child);

    app(DeleteCollection::class)->execute($parent->fresh());
})->throws(CollectionActionException::class);

test('re-parents descendants when a target is provided', function () {
    $parent = Collection::factory()->create(['collection_group_id' => $this->group->id]);
    $child = Collection::factory()->create(['collection_group_id' => $this->group->id]);
    $parent->appendNode($child);

    $newHome = Collection::factory()->create(['collection_group_id' => $this->group->id]);

    app(DeleteCollection::class)->execute($parent->fresh(), $newHome);

    expect(Collection::find($parent->id))->toBeNull();
    expect($child->fresh()->parent_id)->toBe($newHome->id);
});

// Deleting a nested-set node sweeps its descendants by the node's in-memory
// bounds. If the collection handed to the action is stale — its subtree moved
// elsewhere since it was loaded — those bounds still span the moved nodes, and
// the descendant sweep destroys them. The package only auto-corrects the bounds
// when it has performed a node action this request, so we zero that flag to model
// a request where it hasn't and pin the action's correctness independently of it.
test('does not sweep up descendants that moved out from under a stale collection', function () {
    $parent = Collection::factory()->create(['collection_group_id' => $this->group->id]);
    $child = Collection::factory()->create(['collection_group_id' => $this->group->id]);
    $parent->appendNode($child);

    $stale = $parent->fresh();

    $newHome = Collection::factory()->create(['collection_group_id' => $this->group->id]);
    $newHome->appendNode($child);

    Collection::$actionsPerformed = 0;

    app(DeleteCollection::class)->execute($stale);

    expect(Collection::find($parent->id))->toBeNull();
    expect($child->fresh()?->parent_id)->toBe($newHome->id);
    expect(Collection::find($newHome->id))->not->toBeNull();
});
