<?php

use Livewire\Livewire;
use Lunar\Admin\Filament\Resources\AttributeResource;
use Lunar\Admin\Filament\Resources\AttributeResource\Pages\CreateAttribute;
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

it('can render attribute create page', function () {
    $this->asStaff(admin: true)
        ->get(AttributeResource::getUrl('create'))
        ->assertSuccessful();
});

it('can create an ungrouped attribute', function () {
    Livewire::actingAs($this->makeStaff(admin: true), 'staff')
        ->test(CreateAttribute::class)
        ->fillForm([
            'name' => 'Warranty',
            'handle' => 'warranty',
            'attribute_group_id' => null,
            'type' => FieldTypeEnum::Text->value,
            'model_types' => ['product'],
            'configuration' => ['richtext' => false],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas((new Attribute)->getTable(), [
        'name' => 'Warranty',
        'handle' => 'warranty',
        'attribute_group_id' => null,
        'system' => false,
    ]);

    $this->assertDatabaseHas('lunar_attribute_models', [
        'model_type' => 'product',
    ]);
});

it('can create an attribute in a group', function () {
    $group = AttributeGroup::factory()->create();

    Livewire::actingAs($this->makeStaff(admin: true), 'staff')
        ->test(CreateAttribute::class)
        ->fillForm([
            'name' => 'Material',
            'handle' => 'material',
            'attribute_group_id' => $group->id,
            'type' => FieldTypeEnum::Text->value,
            'model_types' => ['product'],
            'configuration' => ['richtext' => false],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas((new Attribute)->getTable(), [
        'name' => 'Material',
        'attribute_group_id' => $group->id,
    ]);
});

it('rejects an attribute mapped to both product and product variant', function () {
    Livewire::actingAs($this->makeStaff(admin: true), 'staff')
        ->test(CreateAttribute::class)
        ->fillForm([
            'name' => 'Size',
            'handle' => 'size',
            'type' => FieldTypeEnum::Text->value,
            'model_types' => ['product', 'product_variant'],
            'configuration' => ['richtext' => false],
        ])
        ->call('create')
        ->assertHasFormErrors(['model_types']);
});
