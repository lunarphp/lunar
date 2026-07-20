<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Actions\Attributes\CreateAttributeGroup;
use Lunar\Core\Models\AttributeGroup;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class);

test('creates an attribute group', function () {
    $group = app(CreateAttributeGroup::class)->execute([
        'name' => 'Details',
        'handle' => 'details',
    ]);

    expect($group)->toBeInstanceOf(AttributeGroup::class);

    $this->assertDatabaseHas('lunar_attribute_groups', [
        'id' => $group->id,
        'name' => 'Details',
        'handle' => 'details',
    ]);
});

test('appends to the end of the position order by default', function () {
    AttributeGroup::factory()->create(['position' => 4]);

    $group = app(CreateAttributeGroup::class)->execute([
        'name' => 'Details',
        'handle' => 'details',
    ]);

    expect($group->position)->toBe(5);
});
