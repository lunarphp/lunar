<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Actions\Attributes\DeleteAttributeGroup;
use Lunar\Core\Exceptions\AttributeActionException;
use Lunar\Core\Models\Attribute;
use Lunar\Core\Models\AttributeGroup;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

test('deletes an empty non-system attribute group', function () {
    $group = AttributeGroup::factory()->create(['system' => false]);

    app(DeleteAttributeGroup::class)->execute($group);

    $this->assertDatabaseMissing('lunar_attribute_groups', ['id' => $group->id]);
});

test('refuses to delete a system attribute group', function () {
    $group = AttributeGroup::factory()->create(['system' => true]);

    expect(fn () => app(DeleteAttributeGroup::class)->execute($group))
        ->toThrow(AttributeActionException::class);

    $this->assertDatabaseHas('lunar_attribute_groups', ['id' => $group->id]);
});

test('refuses to delete a group that still has attributes', function () {
    $group = AttributeGroup::factory()->create(['system' => false]);
    Attribute::factory()->create(['attribute_group_id' => $group->id]);

    expect(fn () => app(DeleteAttributeGroup::class)->execute($group))
        ->toThrow(AttributeActionException::class);

    $this->assertDatabaseHas('lunar_attribute_groups', ['id' => $group->id]);
});
