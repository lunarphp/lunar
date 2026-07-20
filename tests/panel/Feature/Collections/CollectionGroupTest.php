<?php

use Lunar\Core\Models\Collection;
use Lunar\Core\Models\CollectionGroup;
use Lunar\Core\Models\Language;
use Lunar\Core\Models\Staff;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->actingAs(Staff::factory()->create(['admin' => true]), 'staff');

    Language::factory()->create(['default' => true, 'code' => 'en']);
});

it('creates a group, generating the handle when absent', function () {
    $this->post(route('panel.collections.groups.store'), [
        'name' => 'Gift Guides',
    ])->assertRedirect();

    $group = CollectionGroup::sole();

    expect($group->name)->toBe('Gift Guides')
        ->and($group->handle)->toBe('gift-guides');
});

it('renames a group', function () {
    $group = CollectionGroup::factory()->create();

    $this->put(route('panel.collections.groups.update', $group), [
        'name' => 'Campaigns',
        'handle' => 'campaigns',
    ])->assertRedirect();

    $this->assertDatabaseHas('lunar_collection_groups', [
        'id' => $group->id,
        'name' => 'Campaigns',
        'handle' => 'campaigns',
    ]);
});

it('rejects a duplicate handle', function () {
    CollectionGroup::factory()->create(['handle' => 'taken']);
    $group = CollectionGroup::factory()->create();

    $this->put(route('panel.collections.groups.update', $group), [
        'name' => 'Whatever',
        'handle' => 'taken',
    ])->assertSessionHasErrors('handle');
});

it('deletes an empty group', function () {
    $group = CollectionGroup::factory()->create();

    $this->delete(route('panel.collections.groups.destroy', $group))->assertRedirect();

    $this->assertDatabaseMissing('lunar_collection_groups', ['id' => $group->id]);
});

it('refuses to delete a group with collections', function () {
    $group = CollectionGroup::factory()->create();
    Collection::factory()->create(['collection_group_id' => $group->id]);

    $this->delete(route('panel.collections.groups.destroy', $group))
        ->assertSessionHas('error');

    $this->assertDatabaseHas('lunar_collection_groups', ['id' => $group->id]);
});

it('gates group management behind the collections permission', function () {
    $this->actingAs(Staff::factory()->create(['admin' => false]), 'staff');

    $this->post(route('panel.collections.groups.store'), ['name' => 'Nope'])->assertForbidden();
});
