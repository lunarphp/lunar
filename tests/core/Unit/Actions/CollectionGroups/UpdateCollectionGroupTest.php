<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Actions\CollectionGroups\UpdateCollectionGroup;
use Lunar\Core\Models\CollectionGroup;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

test('updates the group name and handle', function () {
    $group = CollectionGroup::factory()->create();

    app(UpdateCollectionGroup::class)->execute($group, [
        'name' => 'Campaigns',
        'handle' => 'campaigns',
    ]);

    $this->assertDatabaseHas('lunar_collection_groups', [
        'id' => $group->id,
        'name' => 'Campaigns',
        'handle' => 'campaigns',
    ]);
});
