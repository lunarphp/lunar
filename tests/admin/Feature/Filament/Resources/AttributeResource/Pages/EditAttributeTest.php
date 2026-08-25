<?php

use Filament\Actions\DeleteAction;
use Livewire\Livewire;
use Lunar\Admin\Filament\Resources\AttributeResource;
use Lunar\Admin\Filament\Resources\AttributeResource\Pages\EditAttribute;
use Lunar\Core\Enums\FieldTypeEnum;
use Lunar\Core\Models\Attribute;
use Lunar\Core\Models\AttributeGroup;
use Lunar\Core\Models\Language;
use Lunar\Tests\Admin\Feature\Filament\TestCase;

uses(TestCase::class)
    ->group('resource.attribute');

beforeEach(function () {
    Language::factory()->create([
        'default' => true,
        'code' => 'en',
    ]);
});

it('can render attribute edit page', function () {
    $this->asStaff(admin: true)
        ->get(AttributeResource::getUrl('edit', ['record' => Attribute::factory()->create()]))
        ->assertSuccessful();
});

it('can render the edit page for an ungrouped attribute', function () {
    $attribute = Attribute::factory()->create([
        'attribute_group_id' => null,
    ]);

    $this->asStaff(admin: true)
        ->get(AttributeResource::getUrl('edit', ['record' => $attribute]))
        ->assertSuccessful();
});

it('hydrates the form with group, model types and configuration', function () {
    $this->asStaff();

    $group = AttributeGroup::factory()->create();

    $attribute = Attribute::factory()->modelType('product')->create([
        'attribute_group_id' => $group->id,
        'type' => FieldTypeEnum::Dropdown->value,
        'configuration' => [
            'lookups' => [
                ['label' => 'Red', 'value' => 'red'],
            ],
        ],
    ]);

    Livewire::test(EditAttribute::class, [
        'record' => $attribute->getRouteKey(),
    ])
        ->assertFormSet([
            'attribute_group_id' => $group->id,
            'model_types' => ['product'],
            'configuration' => [
                'lookups' => [
                    ['key' => 'Red', 'value' => 'red'],
                ],
            ],
        ]);
});

it('can move an attribute out of its group', function () {
    $group = AttributeGroup::factory()->create();

    $attribute = Attribute::factory()->modelType('product')->create([
        'attribute_group_id' => $group->id,
    ]);

    Livewire::actingAs($this->makeStaff(admin: true), 'staff')
        ->test(EditAttribute::class, [
            'record' => $attribute->getRouteKey(),
        ])
        ->fillForm([
            'attribute_group_id' => null,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($attribute->refresh()->attribute_group_id)->toBeNull();
});

it('syncs model types on save', function () {
    $attribute = Attribute::factory()->modelType('product')->create();

    Livewire::actingAs($this->makeStaff(admin: true), 'staff')
        ->test(EditAttribute::class, [
            'record' => $attribute->getRouteKey(),
        ])
        ->fillForm([
            'model_types' => ['collection'],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($attribute->models()->pluck('model_type')->all())->toBe(['collection']);
});

it('can delete a non-system attribute', function () {
    $attribute = Attribute::factory()->create();

    Livewire::actingAs($this->makeStaff(admin: true), 'staff')
        ->test(EditAttribute::class, [
            'record' => $attribute->getRouteKey(),
        ])
        ->callAction(DeleteAction::class);

    $this->assertDatabaseMissing((new Attribute)->getTable(), [
        'id' => $attribute->id,
    ]);
});

it('cannot delete a system attribute', function () {
    $attribute = Attribute::factory()->create([
        'system' => true,
    ]);

    Livewire::actingAs($this->makeStaff(admin: true), 'staff')
        ->test(EditAttribute::class, [
            'record' => $attribute->getRouteKey(),
        ])
        ->callAction(DeleteAction::class);

    $this->assertDatabaseHas((new Attribute)->getTable(), [
        'id' => $attribute->id,
    ]);
});
