<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Actions\Attributes\UpdateAttributeGroup;
use Lunar\Core\Models\AttributeGroup;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

test('updates the attribute group attributes', function () {
    $group = AttributeGroup::factory()->create(['name' => 'Old Name']);

    app(UpdateAttributeGroup::class)->execute($group, ['name' => 'New Name', 'position' => 9]);

    $this->assertDatabaseHas('lunar_attribute_groups', [
        'id' => $group->id,
        'name' => 'New Name',
        'position' => 9,
    ]);
});
