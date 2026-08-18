<?php

use Inertia\Testing\AssertableInertia as Assert;
use Lunar\Core\Models\Attribute;
use Lunar\Core\Models\AttributeGroup;
use Lunar\Core\Models\Staff;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->staff = Staff::factory()->create(['admin' => true]);
    $this->actingAs($this->staff, 'staff');
});

test('the attribute groups index renders ordered by position', function () {
    AttributeGroup::factory()->create(['name' => 'Second', 'handle' => 'second', 'position' => 2]);
    AttributeGroup::factory()->create(['name' => 'First', 'handle' => 'first', 'position' => 1]);

    $this->get(route('panel.settings.attribute-groups.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('settings/attribute-groups/Index')
            ->has('attributeGroups.data', 2)
            ->where('attributeGroups.data.0.name', 'First')
            ->where('attributeGroups.data.1.name', 'Second')
            ->has('urls.store')
        );
});

test('groups carry row actions, with delete omitted for system groups and groups with attributes', function () {
    AttributeGroup::factory()->create(['name' => 'A system group', 'handle' => 'sys', 'system' => true, 'position' => 1]);
    $withAttributes = AttributeGroup::factory()->create(['name' => 'Busy', 'handle' => 'busy', 'position' => 2]);
    Attribute::factory()->create(['attribute_group_id' => $withAttributes->id]);
    AttributeGroup::factory()->create(['name' => 'Empty', 'handle' => 'empty', 'position' => 3]);

    $this->get(route('panel.settings.attribute-groups.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('attributeGroups.data.0._actions', fn ($actions) => ! isset($actions['delete']))
            ->where('attributeGroups.data.1._actions', fn ($actions) => ! isset($actions['delete']))
            ->where('attributeGroups.data.2._actions', fn ($actions) => isset($actions['edit'], $actions['delete']))
        );
});

test('an attribute group can be created with an auto-generated handle and position', function () {
    AttributeGroup::factory()->create(['position' => 4]);

    $this->post(route('panel.settings.attribute-groups.store'), [
        'name' => 'Product Details',
    ])->assertRedirect(route('panel.settings.attribute-groups.index'))
        ->assertSessionHas('success');

    $attributeGroup = AttributeGroup::where('name', 'Product Details')->first();

    expect($attributeGroup)->not->toBeNull();
    expect($attributeGroup->handle)->toBe('product_details');
    expect($attributeGroup->position)->toBe(5);
    expect($attributeGroup->system)->toBeFalse();
});

test('a colliding auto-generated handle is rejected as a validation error', function () {
    AttributeGroup::factory()->create(['handle' => 'product_details']);

    $this->post(route('panel.settings.attribute-groups.store'), [
        'name' => 'Product Details',
    ])->assertSessionHasErrors('handle');

    expect(AttributeGroup::count())->toBe(1);
});

test('the attribute group edit screen renders with its attributes', function () {
    $attributeGroup = AttributeGroup::factory()->create(['name' => 'Details', 'handle' => 'details']);
    Attribute::factory()->create(['attribute_group_id' => $attributeGroup->id, 'name' => 'Material', 'handle' => 'material']);

    $this->get(route('panel.settings.attribute-groups.edit', $attributeGroup))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('settings/attribute-groups/Edit')
            ->where('attributeGroup.name', 'Details')
            ->has('attributes', 1)
            ->where('attributes.0.name', 'Material')
        );
});

test('an attribute group can be updated', function () {
    $attributeGroup = AttributeGroup::factory()->create(['name' => 'Details', 'handle' => 'details', 'position' => 1]);

    $this->put(route('panel.settings.attribute-groups.update', $attributeGroup), [
        'name' => 'Product details',
        'handle' => 'product_details',
        'position' => 3,
    ])->assertRedirect(route('panel.settings.attribute-groups.index'))
        ->assertSessionHas('success');

    $attributeGroup->refresh();

    expect($attributeGroup->name)->toBe('Product details');
    expect($attributeGroup->handle)->toBe('product_details');
    expect($attributeGroup->position)->toBe(3);
});

test('a system group cannot be deleted and shows a flash error', function () {
    $attributeGroup = AttributeGroup::factory()->create(['system' => true]);

    $this->from(route('panel.settings.attribute-groups.edit', $attributeGroup))
        ->delete(route('panel.settings.attribute-groups.destroy', $attributeGroup))
        ->assertRedirect(route('panel.settings.attribute-groups.edit', $attributeGroup))
        ->assertSessionHas('error', __('panel::attribute_groups.delete_blocked_system'));

    expect(AttributeGroup::find($attributeGroup->id))->not->toBeNull();
});

test('a group with attributes cannot be deleted and shows a flash error', function () {
    $attributeGroup = AttributeGroup::factory()->create();
    Attribute::factory()->create(['attribute_group_id' => $attributeGroup->id]);

    $this->from(route('panel.settings.attribute-groups.edit', $attributeGroup))
        ->delete(route('panel.settings.attribute-groups.destroy', $attributeGroup))
        ->assertRedirect(route('panel.settings.attribute-groups.edit', $attributeGroup))
        ->assertSessionHas('error', __('panel::attribute_groups.delete_blocked'));

    expect(AttributeGroup::find($attributeGroup->id))->not->toBeNull();
});

test('an empty non-system group can be deleted', function () {
    $attributeGroup = AttributeGroup::factory()->create(['system' => false]);

    $this->delete(route('panel.settings.attribute-groups.destroy', $attributeGroup))
        ->assertRedirect(route('panel.settings.attribute-groups.index'))
        ->assertSessionHas('success');

    expect(AttributeGroup::find($attributeGroup->id))->toBeNull();
});
