<?php

use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Livewire\Livewire;
use Lunar\Admin\Filament\Resources\AttributeGroupResource\Pages\EditAttributeGroup;
use Lunar\Admin\Filament\Resources\AttributeGroupResource\RelationManagers\AttributesRelationManager;
use Lunar\FieldTypes\Dropdown;
use Lunar\FieldTypes\Number;
use Lunar\FieldTypes\Text;
use Lunar\FieldTypes\Toggle;
use Lunar\Models\Attribute;
use Lunar\Models\AttributeGroup;
use Lunar\Models\Language;
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

it('shows translated attribute names as a single state', function () {
    $this->asStaff();

    $attributeGroup = AttributeGroup::factory()->create();

    $attribute = Attribute::factory()->create([
        'attribute_group_id' => $attributeGroup->id,
        'name' => ['en' => 'Details'],
    ]);

    Livewire::test(AttributesRelationManager::class, [
        'ownerRecord' => $attributeGroup,
        'pageClass' => EditAttributeGroup::class,
    ])
        ->assertCountTableRecords(1)
        ->assertTableColumnStateSet('name', 'Details', $attribute);
});

it('can create attributes', function ($type, $configuration = [], $expectedData = []) {

    $lang = Language::factory()->create([
        'default' => true,
        'code' => 'en',
    ]);

    $this->asStaff();

    $attributeGroup = AttributeGroup::factory()->create();

    Livewire::test(AttributesRelationManager::class, [
        'ownerRecord' => $attributeGroup,
        'pageClass' => EditAttributeGroup::class,
    ])->callTableAction(CreateAction::class, data: [
        'name.'.$lang->code => 'Foobar',
        'type' => $type,
        'handle' => 'foobar',
        'configuration' => $configuration,
    ])->assertHasNoTableActionErrors();

    $this->assertDatabaseHas((new Attribute)->getTable(), [
        'attribute_group_id' => $attributeGroup->id,
        'name' => '{"en":"Foobar"}',
        'handle' => 'foobar',
        'configuration' => $expectedData,
    ]);
})->with([
    'text' => [
        Text::class,
        ['richtext' => false],
        '{"richtext":false}',
    ],
    'richtext' => [
        Text::class,
        ['richtext' => true],
        '{"richtext":true}',
    ],
    'dropdown' => [
        Dropdown::class,
        [],
        '{"lookups":[]}',
    ],
    'dropdown-with-lookups' => [
        Dropdown::class,
        ['lookups' => ['Foo' => 'foo', 'Bar' => 'bar']],
        '{"lookups":[{"label":"Foo","value":"foo"},{"label":"Bar","value":"bar"}]}',
    ],
    'number' => [
        Number::class,
        [],
        '{"min":null,"max":null}',
    ],
    'number-with-min-max' => [
        Number::class,
        ['min' => 5, 'max' => 10],
        '{"min":5,"max":10}',
    ],
]);

it('rejects duplicate handles across groups of the same attribute type', function () {
    Language::factory()->create([
        'default' => true,
        'code' => 'en',
    ]);

    $this->asStaff();

    $groupOne = AttributeGroup::factory()->create([
        'attributable_type' => 'product',
    ]);

    $groupTwo = AttributeGroup::factory()->create([
        'attributable_type' => 'product',
    ]);

    Attribute::factory()->create([
        'attribute_group_id' => $groupOne->id,
        'attribute_type' => 'product',
        'handle' => 'size',
    ]);

    Livewire::test(AttributesRelationManager::class, [
        'ownerRecord' => $groupTwo,
        'pageClass' => EditAttributeGroup::class,
    ])->callTableAction(CreateAction::class, data: [
        'name.en' => 'Size',
        'type' => Text::class,
        'handle' => 'size',
        'configuration' => ['richtext' => false],
    ])->assertHasTableActionErrors(['handle']);
});

it('allows duplicate handles across groups of different attribute types', function () {
    Language::factory()->create([
        'default' => true,
        'code' => 'en',
    ]);

    $this->asStaff();

    $productGroup = AttributeGroup::factory()->create([
        'attributable_type' => 'product',
    ]);

    $collectionGroup = AttributeGroup::factory()->create([
        'attributable_type' => 'collection',
    ]);

    Attribute::factory()->create([
        'attribute_group_id' => $productGroup->id,
        'attribute_type' => 'product',
        'handle' => 'size',
    ]);

    Livewire::test(AttributesRelationManager::class, [
        'ownerRecord' => $collectionGroup,
        'pageClass' => EditAttributeGroup::class,
    ])->callTableAction(CreateAction::class, data: [
        'name.en' => 'Size',
        'type' => Text::class,
        'handle' => 'size',
        'configuration' => ['richtext' => false],
    ])->assertHasNoTableActionErrors();
});

it('can create an attribute with a default value', function () {
    $lang = Language::factory()->create([
        'default' => true,
        'code' => 'en',
    ]);

    $this->asStaff();

    $attributeGroup = AttributeGroup::factory()->create();

    Livewire::test(AttributesRelationManager::class, [
        'ownerRecord' => $attributeGroup,
        'pageClass' => EditAttributeGroup::class,
    ])->callTableAction(CreateAction::class, data: [
        'name.'.$lang->code => 'Price',
        'type' => Number::class,
        'handle' => 'price',
        'default_value' => '99',
        'configuration' => [],
    ])->assertHasNoTableActionErrors();

    $this->assertDatabaseHas((new Attribute)->getTable(), [
        'attribute_group_id' => $attributeGroup->id,
        'handle' => 'price',
        'default_value' => '99',
    ]);
});

it('can edit an attribute default value', function () {
    Language::factory()->create([
        'default' => true,
        'code' => 'en',
    ]);

    $this->asStaff();

    $attributeGroup = AttributeGroup::factory()->create();

    $attribute = Attribute::factory()->create([
        'attribute_group_id' => $attributeGroup->id,
        'type' => Number::class,
        'default_value' => null,
    ]);

    Livewire::test(AttributesRelationManager::class, [
        'ownerRecord' => $attributeGroup,
        'pageClass' => EditAttributeGroup::class,
    ])->callTableAction(EditAction::class, $attribute, data: [
        'default_value' => '42',
    ])->assertHasNoTableActionErrors();

    $this->assertDatabaseHas((new Attribute)->getTable(), [
        'id' => $attribute->id,
        'default_value' => '42',
    ]);
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
        'type' => Dropdown::class,
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

it('rejects a non-numeric default value for number attributes', function () {
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
        'name.en' => 'Quantity',
        'type' => Number::class,
        'handle' => 'quantity',
        'default_value' => 'abc',
        'configuration' => [],
    ])->assertHasTableActionErrors(['default_value']);
});

it('validates a dropdown default value against the configured lookups', function () {
    Language::factory()->create([
        'default' => true,
        'code' => 'en',
    ]);

    $this->asStaff();

    $attributeGroup = AttributeGroup::factory()->create();

    $lookups = [
        ['key' => 'Red', 'value' => 'red'],
        ['key' => 'Blue', 'value' => 'blue'],
    ];

    Livewire::test(AttributesRelationManager::class, [
        'ownerRecord' => $attributeGroup,
        'pageClass' => EditAttributeGroup::class,
    ])->callTableAction(CreateAction::class, data: [
        'name.en' => 'Colour',
        'type' => Dropdown::class,
        'handle' => 'colour',
        'default_value' => 'green',
        'configuration' => [
            'lookups' => $lookups,
        ],
    ])->assertHasTableActionErrors(['default_value']);

    Livewire::test(AttributesRelationManager::class, [
        'ownerRecord' => $attributeGroup,
        'pageClass' => EditAttributeGroup::class,
    ])->callTableAction(CreateAction::class, data: [
        'name.en' => 'Colour',
        'type' => Dropdown::class,
        'handle' => 'colour',
        'default_value' => 'blue',
        'configuration' => [
            'lookups' => $lookups,
        ],
    ])->assertHasNoTableActionErrors();

    $this->assertDatabaseHas((new Attribute)->getTable(), [
        'handle' => 'colour',
        'default_value' => 'blue',
    ]);
});

it('hides the default value field for field types that do not support one', function () {
    Language::factory()->create([
        'default' => true,
        'code' => 'en',
    ]);

    $this->asStaff();

    $attributeGroup = AttributeGroup::factory()->create();

    $attribute = Attribute::factory()->create([
        'attribute_group_id' => $attributeGroup->id,
        'type' => Toggle::class,
    ]);

    Livewire::test(AttributesRelationManager::class, [
        'ownerRecord' => $attributeGroup,
        'pageClass' => EditAttributeGroup::class,
    ])
        ->mountTableAction(EditAction::class, $attribute)
        ->assertFormFieldHidden('default_value');
});

it('shows the default value field for field types that support one', function () {
    Language::factory()->create([
        'default' => true,
        'code' => 'en',
    ]);

    $this->asStaff();

    $attributeGroup = AttributeGroup::factory()->create();

    $attribute = Attribute::factory()->create([
        'attribute_group_id' => $attributeGroup->id,
        'type' => Text::class,
    ]);

    Livewire::test(AttributesRelationManager::class, [
        'ownerRecord' => $attributeGroup,
        'pageClass' => EditAttributeGroup::class,
    ])
        ->mountTableAction(EditAction::class, $attribute)
        ->assertFormFieldVisible('default_value');
});
