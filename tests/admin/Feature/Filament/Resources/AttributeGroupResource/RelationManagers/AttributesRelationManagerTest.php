<?php

use Filament\Actions\CreateAction;
use Livewire\Livewire;
use Lunar\Admin\Filament\Resources\AttributeGroupResource\Pages\EditAttributeGroup;
use Lunar\Admin\Filament\Resources\AttributeGroupResource\RelationManagers\AttributesRelationManager;
use Lunar\FieldTypes\Dropdown;
use Lunar\FieldTypes\Number;
use Lunar\FieldTypes\Text;
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
