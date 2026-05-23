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

    MoveCollection::run($a, $b);

    expect($a->fresh()->parent_id)->toBe($b->id);
});

test('makes a collection a root when target is null', function () {
    $parent = Collection::factory()->create(['collection_group_id' => $this->group->id]);
    $child = Collection::factory()->create(['collection_group_id' => $this->group->id]);
    $parent->appendNode($child);

    MoveCollection::run($child->fresh(), null);

    expect($child->fresh()->parent_id)->toBeNull();
});

test('rejects moving a collection into itself', function () {
    $a = Collection::factory()->create(['collection_group_id' => $this->group->id]);

    MoveCollection::run($a, $a);
})->throws(CollectionActionException::class);

test('rejects moving a collection into one of its descendants', function () {
    $parent = Collection::factory()->create(['collection_group_id' => $this->group->id]);
    $child = Collection::factory()->create(['collection_group_id' => $this->group->id]);
    $parent->appendNode($child);

    MoveCollection::run($parent->fresh(), $child->fresh());
})->throws(CollectionActionException::class);
