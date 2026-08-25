<?php

use Inertia\Testing\AssertableInertia as Assert;
use Lunar\Core\Facades\FieldTypeManifest;
use Lunar\Core\FieldTypes\AbstractFieldType;
use Lunar\Core\Models\Attribute;
use Lunar\Core\Models\AttributeGroup;
use Lunar\Core\Models\Product;
use Lunar\Core\Models\ProductVariant;
use Lunar\Core\Models\Staff;
use Lunar\Tests\Panel\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->staff = Staff::factory()->create(['admin' => true]);
    $this->actingAs($this->staff, 'staff');
});

/** A consumer-registered field type carrying its own configuration contract. */
class StarRatingFieldType extends AbstractFieldType
{
    public function getConfig(): array
    {
        return ['options' => ['scale' => 'nullable|numeric']];
    }

    public function getConfigurationFields(): array
    {
        return [
            ['key' => 'scale', 'type' => 'number', 'label' => 'Scale'],
        ];
    }
}

test('the attributes index renders with groups, field types, and model types', function () {
    $group = AttributeGroup::factory()->create(['name' => 'Details']);
    Attribute::factory()->create(['name' => 'Material', 'handle' => 'material', 'attribute_group_id' => $group->id, 'position' => 1]);
    Attribute::factory()->create(['name' => 'Weight', 'handle' => 'weight', 'attribute_group_id' => null, 'position' => 2]);

    $this->get(route('panel.settings.attributes.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('settings/attributes/Index')
            ->has('attributes.data', 2)
            ->where('attributes.data.0.name', 'Material')
            ->where('attributes.data.0.group', 'Details')
            ->where('attributes.data.1.group', null)
            ->has('fieldTypes')
            ->has('modelTypes')
            ->has('urls.store')
        );
});

test('the attributes index can be filtered by group', function () {
    $group = AttributeGroup::factory()->create();
    Attribute::factory()->create(['name' => 'Material', 'attribute_group_id' => $group->id]);
    Attribute::factory()->create(['name' => 'Weight', 'attribute_group_id' => null]);

    $this->get(route('panel.settings.attributes.index', ['attribute_group_id' => $group->id]))
        ->assertInertia(fn (Assert $page) => $page
            ->has('attributes.data', 1)
            ->where('attributes.data.0.name', 'Material')
        );
});

test('attributes carry row actions, with delete omitted for system attributes', function () {
    Attribute::factory()->create(['name' => 'Name', 'system' => true, 'position' => 1]);
    Attribute::factory()->create(['name' => 'Material', 'system' => false, 'position' => 2]);

    $this->get(route('panel.settings.attributes.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('attributes.data.0._actions', fn ($actions) => isset($actions['edit']) && ! isset($actions['delete']))
            ->where('attributes.data.1._actions', fn ($actions) => isset($actions['edit'], $actions['delete']))
        );
});

test('an attribute can be created with model types', function () {
    $group = AttributeGroup::factory()->create();

    $this->post(route('panel.settings.attributes.store'), [
        'name' => 'Material',
        'attribute_group_id' => $group->id,
        'type' => 'text',
        'model_types' => [Product::morphName()],
    ])->assertRedirect()
        ->assertSessionHas('success');

    $attribute = Attribute::where('handle', 'material')->first();

    expect($attribute)->not->toBeNull();
    expect($attribute->type)->toBe('text');
    expect($attribute->attribute_group_id)->toBe($group->id);
    expect($attribute->system)->toBeFalse();
    expect($attribute->models()->pluck('model_type')->all())->toBe([Product::morphName()]);
});

test('a colliding auto-generated handle is rejected as a validation error', function () {
    Attribute::factory()->create(['handle' => 'material']);

    $this->post(route('panel.settings.attributes.store'), [
        'name' => 'Material',
        'type' => 'text',
        'model_types' => [Product::morphName()],
    ])->assertSessionHasErrors('handle');

    expect(Attribute::count())->toBe(1);
});

test('an attribute cannot apply to both products and variants', function () {
    $this->post(route('panel.settings.attributes.store'), [
        'name' => 'Material',
        'type' => 'text',
        'model_types' => [Product::morphName(), ProductVariant::morphName()],
    ])->assertSessionHasErrors('model_types');
});

test('the type must be a known field type', function () {
    $this->post(route('panel.settings.attributes.store'), [
        'name' => 'Material',
        'type' => 'hologram',
        'model_types' => [Product::morphName()],
    ])->assertSessionHasErrors('type');
});

test('the attribute edit screen renders with the attribute data', function () {
    $attribute = Attribute::factory()->create([
        'name' => 'Material',
        'handle' => 'material',
        'type' => 'dropdown',
        'configuration' => ['lookups' => [['label' => 'Wood', 'value' => 'wood']]],
    ]);
    $attribute->models()->create(['model_type' => Product::morphName()]);

    $this->get(route('panel.settings.attributes.edit', $attribute))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('settings/attributes/Edit')
            ->where('attribute.name', 'Material')
            ->where('attribute.type', 'dropdown')
            ->where('attribute.configuration.lookups.0.label', 'Wood')
            ->where('attribute.configuration.lookups.0.value', 'wood')
            ->where('attribute.model_types.0', Product::morphName())
            ->has('urls.update')
        );
});

test('an attribute can be updated, including configuration and model types', function () {
    $attribute = Attribute::factory()->create([
        'name' => 'Material',
        'handle' => 'material',
        'type' => 'dropdown',
        'required' => false,
    ]);
    $attribute->models()->create(['model_type' => Product::morphName()]);

    $this->put(route('panel.settings.attributes.update', $attribute), [
        'name' => 'Materials',
        'handle' => 'material',
        'required' => true,
        'model_types' => [ProductVariant::morphName()],
        'configuration' => ['lookups' => [['label' => 'Wood', 'value' => 'wood'], ['label' => 'Steel', 'value' => 'steel']]],
    ])->assertRedirect(route('panel.settings.attributes.index'))
        ->assertSessionHas('success');

    $attribute->refresh();

    expect($attribute->name)->toBe('Materials');
    expect($attribute->required)->toBeTrue();
    expect($attribute->configuration['lookups'])->toBe([['label' => 'Wood', 'value' => 'wood'], ['label' => 'Steel', 'value' => 'steel']]);
    expect($attribute->models()->pluck('model_type')->all())->toBe([ProductVariant::morphName()]);
});

test('dropdown lookups submitted as a label => value map are rejected', function () {
    $attribute = Attribute::factory()->create(['type' => 'dropdown']);
    $attribute->models()->create(['model_type' => Product::morphName()]);

    $this->put(route('panel.settings.attributes.update', $attribute), [
        'name' => $attribute->name,
        'handle' => $attribute->handle,
        'model_types' => [Product::morphName()],
        'configuration' => ['lookups' => ['Wood' => 'wood']],
    ])->assertSessionHasErrors('configuration.lookups.Wood.label');
});

test('the edit screen serves the field type\'s configuration descriptors', function () {
    $attribute = Attribute::factory()->create(['type' => 'number']);
    $attribute->models()->create(['model_type' => Product::morphName()]);

    $this->get(route('panel.settings.attributes.edit', $attribute))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('configFields', 2)
            ->where('configFields.0.key', 'min')
            ->where('configFields.0.type', 'number')
            ->where('configFields.1.key', 'max')
        );
});

test('configuration is validated using the field type\'s declared rules', function () {
    $attribute = Attribute::factory()->create(['type' => 'number']);
    $attribute->models()->create(['model_type' => Product::morphName()]);

    $this->put(route('panel.settings.attributes.update', $attribute), [
        'name' => $attribute->name,
        'handle' => $attribute->handle,
        'model_types' => [Product::morphName()],
        'configuration' => ['min' => 'not-a-number'],
    ])->assertSessionHasErrors('configuration.min');

    $this->put(route('panel.settings.attributes.update', $attribute), [
        'name' => $attribute->name,
        'handle' => $attribute->handle,
        'model_types' => [Product::morphName()],
        'configuration' => ['min' => '2', 'max' => '10'],
    ])->assertSessionDoesntHaveErrors();

    expect($attribute->refresh()->configuration['min'])->toBe('2');
});

test('a custom registered field type exposes its own configuration descriptors and rules', function () {
    FieldTypeManifest::add('stars', StarRatingFieldType::class);

    $attribute = Attribute::factory()->create(['type' => 'stars']);
    $attribute->models()->create(['model_type' => Product::morphName()]);

    $this->get(route('panel.settings.attributes.edit', $attribute))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('configFields', 1)
            ->where('configFields.0.key', 'scale')
            ->where('configFields.0.type', 'number')
            ->where('configFields.0.label', 'Scale')
        );

    $this->put(route('panel.settings.attributes.update', $attribute), [
        'name' => $attribute->name,
        'handle' => $attribute->handle,
        'model_types' => [Product::morphName()],
        'configuration' => ['scale' => 'ten'],
    ])->assertSessionHasErrors('configuration.scale');

    $this->put(route('panel.settings.attributes.update', $attribute), [
        'name' => $attribute->name,
        'handle' => $attribute->handle,
        'model_types' => [Product::morphName()],
        'configuration' => ['scale' => '5'],
    ])->assertSessionDoesntHaveErrors();

    expect($attribute->refresh()->configuration['scale'])->toBe('5');
});

test('the type cannot be changed after creation', function () {
    $attribute = Attribute::factory()->create(['type' => 'text']);

    $this->put(route('panel.settings.attributes.update', $attribute), [
        'name' => $attribute->name,
        'type' => 'number',
        'model_types' => [Product::morphName()],
    ])->assertSessionHasErrors('type');

    expect($attribute->fresh()->type)->toBe('text');
});

test('a system attribute cannot be deleted and shows a flash error', function () {
    $attribute = Attribute::factory()->create(['system' => true]);

    $this->from(route('panel.settings.attributes.edit', $attribute))
        ->delete(route('panel.settings.attributes.destroy', $attribute))
        ->assertRedirect(route('panel.settings.attributes.edit', $attribute))
        ->assertSessionHas('error', __('panel::attributes_settings.delete_blocked_system'));

    expect(Attribute::find($attribute->id))->not->toBeNull();
});

test('a non-system attribute can be deleted along with its model links', function () {
    $attribute = Attribute::factory()->create(['system' => false]);
    $attribute->models()->create(['model_type' => Product::morphName()]);

    $this->delete(route('panel.settings.attributes.destroy', $attribute))
        ->assertRedirect(route('panel.settings.attributes.index'))
        ->assertSessionHas('success');

    expect(Attribute::find($attribute->id))->toBeNull();
});

test('an attribute can be updated with validation rules, and clearing them stores null', function () {
    $attribute = Attribute::factory()->create(['type' => 'text']);
    $attribute->models()->create(['model_type' => Product::morphName()]);

    $payload = [
        'name' => $attribute->name,
        'handle' => $attribute->handle,
        'model_types' => [Product::morphName()],
    ];

    $this->put(route('panel.settings.attributes.update', $attribute), [
        ...$payload,
        'validation_rules' => ['min:2', 'max:10', ' '],
    ])->assertRedirect(route('panel.settings.attributes.index'));

    // Blank entries are dropped on the way in.
    expect($attribute->refresh()->validation_rules)->toBe(['min:2', 'max:10']);

    $this->put(route('panel.settings.attributes.update', $attribute), [
        ...$payload,
        'validation_rules' => [],
    ])->assertRedirect(route('panel.settings.attributes.index'));

    expect($attribute->refresh()->validation_rules)->toBeNull();
});

test('validation rules must be an array of strings', function () {
    $attribute = Attribute::factory()->create(['type' => 'text']);
    $attribute->models()->create(['model_type' => Product::morphName()]);

    $this->put(route('panel.settings.attributes.update', $attribute), [
        'name' => $attribute->name,
        'handle' => $attribute->handle,
        'model_types' => [Product::morphName()],
        'validation_rules' => 'min:2|max:10',
    ])->assertSessionHasErrors('validation_rules');
});
