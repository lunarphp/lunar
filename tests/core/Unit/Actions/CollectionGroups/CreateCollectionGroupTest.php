<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Actions\CollectionGroups\CreateCollectionGroup;
use Lunar\Core\Models\CollectionGroup;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

test('creates a collection group with the given attributes', function () {
    $group = app(CreateCollectionGroup::class)->execute([
        'name' => 'Seasonal',
        'handle' => 'seasonal',
    ]);

    expect($group)->toBeInstanceOf(CollectionGroup::class);

    $this->assertDatabaseHas('lunar_collection_groups', [
        'id' => $group->id,
        'name' => 'Seasonal',
        'handle' => 'seasonal',
    ]);
});

test('generates a handle from the name when none is given', function () {
    $group = app(CreateCollectionGroup::class)->execute([
        'name' => 'Gift Guides',
    ]);

    expect($group->handle)->toBe('gift-guides');
});

test('suffixes a generated handle until unique', function () {
    CollectionGroup::factory()->create(['handle' => 'gift-guides']);
    CollectionGroup::factory()->create(['handle' => 'gift-guides-2']);

    $group = app(CreateCollectionGroup::class)->execute([
        'name' => 'Gift Guides',
    ]);

    expect($group->handle)->toBe('gift-guides-3');
});
