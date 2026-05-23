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

    expect(DeleteCollection::run($collection))->toBeTrue();
    expect(Collection::find($collection->id))->toBeNull();
});

test('rejects deleting a collection with descendants without a target', function () {
    $parent = Collection::factory()->create(['collection_group_id' => $this->group->id]);
    $child = Collection::factory()->create(['collection_group_id' => $this->group->id]);
    $parent->appendNode($child);

    DeleteCollection::run($parent->fresh());
})->throws(CollectionActionException::class);

test('re-parents descendants when a target is provided', function () {
    $parent = Collection::factory()->create(['collection_group_id' => $this->group->id]);
    $child = Collection::factory()->create(['collection_group_id' => $this->group->id]);
    $parent->appendNode($child);

    $newHome = Collection::factory()->create(['collection_group_id' => $this->group->id]);

    DeleteCollection::run($parent->fresh(), $newHome);

    expect(Collection::find($parent->id))->toBeNull();
    expect($child->fresh()->parent_id)->toBe($newHome->id);
});
