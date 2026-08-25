<?php

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Livewire\Livewire;
use Lunar\Admin\Filament\Resources\AttributeGroupResource\Pages\EditAttributeGroup;
use Lunar\Core\Enums\FieldTypeEnum;
use Lunar\Core\Models\Attribute;
use Lunar\Core\Models\AttributeGroup;
use Lunar\Core\Models\Language;
use Lunar\Filament\RelationManagers\AttributeGroup\AttributesRelationManager;
use Lunar\Tests\Admin\Feature\Filament\TestCase;

uses(TestCase::class)
    ->group('resource.attribute-group');

it('can render relation manager', function () {

    $this->asStaff();

    $attributeGroup = AttributeGroup::factory()->create();

    Livewire::test(AttributesRelationManager::class, [
        'ownerRecord' => $attributeGroup,
        'pageClass' => EditAttributeGroup::class,
    ])->assertSuccessful();
});

it('shows attribute names', function () {
    $this->asStaff();

    $attributeGroup = AttributeGroup::factory()->create();

    $attribute = Attribute::factory()->create([
        'attribute_group_id' => $attributeGroup->id,
        'name' => 'Details',
    ]);

    Livewire::test(AttributesRelationManager::class, [
        'ownerRecord' => $attributeGroup,
        'pageClass' => EditAttributeGroup::class,
    ])
        ->assertCountTableRecords(1)
        ->assertTableColumnStateSet('name', 'Details', $attribute);
});

it('can create attributes', function ($type, $configuration = [], $expectedData = []) {

    Language::factory()->create([
        'default' => true,
        'code' => 'en',
    ]);

    $this->asStaff();

    $attributeGroup = AttributeGroup::factory()->create();

    Livewire::test(AttributesRelationManager::class, [
        'ownerRecord' => $attributeGroup,
        'pageClass' => EditAttributeGroup::class,
    ])->callTableAction(CreateAction::class, data: [
        'name' => 'Foobar',
        'type' => $type,
        'model_types' => ['product'],
        'handle' => 'foobar',
        'configuration' => $configuration,
    ])->assertHasNoTableActionErrors();

    $this->assertDatabaseHas((new Attribute)->getTable(), [
        'attribute_group_id' => $attributeGroup->id,
        'name' => 'Foobar',
        'handle' => 'foobar',
        'configuration' => $expectedData,
    ]);

    $this->assertDatabaseHas('lunar_attribute_models', [
        'model_type' => 'product',
    ]);
})->with([
    'text' => [
        FieldTypeEnum::Text->value,
        ['richtext' => false],
        '{"richtext":false}',
    ],
    'richtext' => [
        FieldTypeEnum::Text->value,
        ['richtext' => true],
        '{"richtext":true}',
    ],
    'dropdown' => [
        FieldTypeEnum::Dropdown->value,
        [],
        '{"lookups":[]}',
    ],
    'dropdown-with-lookups' => [
        FieldTypeEnum::Dropdown->value,
        ['lookups' => ['Foo' => 'foo', 'Bar' => 'bar']],
        '{"lookups":[{"label":"Foo","value":"foo"},{"label":"Bar","value":"bar"}]}',
    ],
    'number' => [
        FieldTypeEnum::Number->value,
        [],
        '{"min":null,"max":null}',
    ],
    'number-with-min-max' => [
        FieldTypeEnum::Number->value,
        ['min' => 5, 'max' => 10],
        '{"min":5,"max":10}',
    ],
]);

it('rejects an attribute mapped to both product and product variant', function () {
    Language::factory()->create([
        'default' => true,
        'code' => 'en',
    ]);

    $this->asStaff();

    $attributeGroup = AttributeGroup::factory()->create();

    Livewire::test(AttributesRelationManager::class, [
        'ownerRecord' => $attributeGroup,
        'pageClass' => EditAttributeGroup::class,
    ])->callTableAction(CreateAction::class, data: [
        'name' => 'Size',
        'type' => FieldTypeEnum::Text->value,
        'model_types' => ['product', 'product_variant'],
        'handle' => 'size',
        'configuration' => ['richtext' => false],
    ])->assertHasTableActionErrors(['model_types']);
});

it('rejects duplicate handles', function () {
    Language::factory()->create([
        'default' => true,
        'code' => 'en',
    ]);

    $this->asStaff();

    $groupOne = AttributeGroup::factory()->create();
    $groupTwo = AttributeGroup::factory()->create();

    Attribute::factory()->create([
        'attribute_group_id' => $groupOne->id,
        'handle' => 'size',
    ]);

    Livewire::test(AttributesRelationManager::class, [
        'ownerRecord' => $groupTwo,
        'pageClass' => EditAttributeGroup::class,
    ])->callTableAction(CreateAction::class, data: [
        'name' => 'Size',
        'type' => FieldTypeEnum::Text->value,
        'model_type' => 'collection',
        'handle' => 'size',
        'configuration' => ['richtext' => false],
    ])->assertHasTableActionErrors(['handle']);
});

it('hydrates dropdown lookups when editing attributes', function () {
    Language::factory()->create([
        'default' => true,
        'code' => 'en',
    ]);

    $this->asStaff();

    $attributeGroup = AttributeGroup::factory()->create();

    $attribute = Attribute::factory()->create([
        'attribute_group_id' => $attributeGroup->id,
        'type' => FieldTypeEnum::Dropdown->value,
        'configuration' => [
            'lookups' => [
                ['label' => 'aaaa', 'value' => 'bbbb'],
            ],
        ],
    ]);

    Livewire::test(AttributesRelationManager::class, [
        'ownerRecord' => $attributeGroup,
        'pageClass' => EditAttributeGroup::class,
    ])
        ->mountTableAction(EditAction::class, $attribute)
        ->assertSet('mountedActions.0.data.configuration.lookups', [
            [
                'key' => 'aaaa',
                'value' => 'bbbb',
            ],
        ])
        ->assertTableActionDataSet([
            'configuration' => [
                'lookups' => [
                    [
                        'key' => 'aaaa',
                        'value' => 'bbbb',
                    ],
                ],
            ],
        ]);
});

it('cannot delete a system attribute from the relation manager', function () {
    $this->asStaff();

    $attributeGroup = AttributeGroup::factory()->create();

    $attribute = Attribute::factory()->create([
        'attribute_group_id' => $attributeGroup->id,
        'system' => true,
    ]);

    Livewire::test(AttributesRelationManager::class, [
        'ownerRecord' => $attributeGroup,
        'pageClass' => EditAttributeGroup::class,
    ])->callTableAction(DeleteAction::class, $attribute);

    $this->assertDatabaseHas((new Attribute)->getTable(), [
        'id' => $attribute->id,
    ]);
});

it('can delete a non-system attribute from the relation manager', function () {
    $this->asStaff();

    $attributeGroup = AttributeGroup::factory()->create();

    $attribute = Attribute::factory()->create([
        'attribute_group_id' => $attributeGroup->id,
    ]);

    Livewire::test(AttributesRelationManager::class, [
        'ownerRecord' => $attributeGroup,
        'pageClass' => EditAttributeGroup::class,
    ])->callTableAction(DeleteAction::class, $attribute);

    $this->assertDatabaseMissing((new Attribute)->getTable(), [
        'id' => $attribute->id,
    ]);
});
