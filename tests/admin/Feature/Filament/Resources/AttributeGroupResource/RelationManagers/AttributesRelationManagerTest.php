<?php

use Livewire\Livewire;
use Lunar\Admin\Filament\Resources\AttributeGroupResource\Pages\EditAttributeGroup;
use Lunar\Admin\Filament\Resources\AttributeGroupResource\RelationManagers\AttributesRelationManager;
use Lunar\Models\AttributeGroup;

uses(\Lunar\Tests\Admin\Feature\Filament\TestCase::class)
    ->group('resource.attribute-group');

it('can render relation manager', function () {
    $this->asStaff();

    $attributeGroup = AttributeGroup::factory()->create();

    Livewire::test(AttributesRelationManager::class, [
        'ownerRecord' => $attributeGroup,
        'pageClass' => EditAttributeGroup::class,
    ])->assertSuccessful();
});

it('can create attributes', function ($type, $configuration = [], $expectedData = []) {
    $this->asStaff();

    $attributeGroup = AttributeGroup::factory()->create();

    Livewire::test(AttributesRelationManager::class, [
        'ownerRecord' => $attributeGroup,
        'pageClass' => EditAttributeGroup::class,
    ])->callTableAction(\Filament\Actions\CreateAction::class, data: [
        'name.en' => 'Foobar', // TODO: Use translation
        'type' => $type,
        'handle' => 'foobar',
        'configuration' => $configuration,
    ])->assertHasNoTableActionErrors();

    $this->assertDatabaseHas((new \Lunar\Models\Attribute)->getTable(), [
        'attribute_group_id' => $attributeGroup->id,
        'name' => '{"en":"Foobar"}',
        'handle' => 'foobar',
        'configuration' => $expectedData,
    ]);
})->with([
    'text' => [
        \Lunar\FieldTypes\Text::class,
        ['richtext' => false],
        '{"richtext":false}',
    ],
    'richtext' => [
        \Lunar\FieldTypes\Text::class,
        ['richtext' => true],
        '{"richtext":true}',
    ],
    'dropdown' => [
        \Lunar\FieldTypes\Dropdown::class,
        [],
        '{"lookups":[]}',
    ],
    'dropdown-with-lookups' => [
        \Lunar\FieldTypes\Dropdown::class,
        ['lookups' => ['Foo' => 'foo', 'Bar' => 'bar']],
        '{"lookups":[{"label":"Foo","value":"foo"},{"label":"Bar","value":"bar"}]}',
    ],
    'number' => [
        \Lunar\FieldTypes\Number::class,
        [],
        '{"min":null,"max":null}',
    ],
    'number-with-min-max' => [
        \Lunar\FieldTypes\Number::class,
        ['min' => 5, 'max' => 10],
        '{"min":5,"max":10}',
    ],
]);
