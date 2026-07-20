<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Actions\CollectionGroups\DeleteCollectionGroup;
use Lunar\Core\Exceptions\CollectionGroupActionException;
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
});

test('deletes an empty collection group', function () {
    $group = CollectionGroup::factory()->create();

    app(DeleteCollectionGroup::class)->execute($group);

    $this->assertDatabaseMissing('lunar_collection_groups', ['id' => $group->id]);
});

test('refuses to delete a group with collections', function () {
    $group = CollectionGroup::factory()->create();
    Collection::factory()->create(['collection_group_id' => $group->id]);

    expect(DeleteCollectionGroup::isProtected($group))->toBeTrue();

    expect(fn () => app(DeleteCollectionGroup::class)->execute($group))
        ->toThrow(CollectionGroupActionException::class);

    $this->assertDatabaseHas('lunar_collection_groups', ['id' => $group->id]);
});

test('the model deleting hook enforces the guard on direct deletes', function () {
    $group = CollectionGroup::factory()->create();
    Collection::factory()->create(['collection_group_id' => $group->id]);

    expect(fn () => $group->delete())
        ->toThrow(CollectionGroupActionException::class);
});
