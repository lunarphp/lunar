<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Lunar\Core\Contracts\FieldType;
use Lunar\Core\Enums\FieldTypeEnum;
use Lunar\Core\FieldTypes\Text;
use Lunar\Core\Models\Attribute;
use Lunar\Core\Models\AttributeGroup;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class)->group('models');

use function Pest\Laravel\assertDatabaseMissing;

uses(RefreshDatabase::class);

test('can make a attribute', function () {
    $options = [
        'Red',
        'Blue',
        'Green',
    ];

    $attribute = Attribute::factory()
        ->for(AttributeGroup::factory(), 'group')
        ->create([
            'position' => 4,
            'name' => 'Meta Description',
            'handle' => 'meta_description',
            'type' => FieldTypeEnum::Text->value,
            'required' => false,
            'configuration' => [
                'options' => $options,
            ],
            'system' => true,
        ]);

    expect($attribute->name)->toEqual('Meta Description');
    expect($attribute->handle)->toEqual('meta_description');
    expect($attribute->type)->toEqual('text');
    expect($attribute->system)->toBeTrue();
    expect($attribute->position)->toEqual(4);
    expect($attribute->configuration->get('options'))->toEqual($options);
});

test('handle is slugified', function () {
    $attribute = Attribute::factory()->create([
        'handle' => 'Meta Description',
    ]);

    expect($attribute->handle)->toEqual('meta_description');
});

test('can resolve the field type instance', function () {
    $attribute = Attribute::factory()->create([
        'type' => FieldTypeEnum::Text->value,
    ]);

    expect($attribute->fieldType())->toBeInstanceOf(FieldType::class);
    expect($attribute->fieldType())->toBeInstanceOf(Text::class);
});

test('belongs to a group', function () {
    $group = AttributeGroup::factory()->create();

    $attribute = Attribute::factory()->create([
        'attribute_group_id' => $group->id,
    ]);

    expect($attribute->group->id)->toEqual($group->id);
});

test('nulls the group when the group is deleted', function () {
    $group = AttributeGroup::factory()->create();

    $attribute = Attribute::factory()->create([
        'attribute_group_id' => $group->id,
    ]);

    $group->delete();

    expect($attribute->refresh()->attribute_group_id)->toBeNull();
});

test('can delete an attribute', function () {
    $attribute = Attribute::factory()->create();

    $attribute->models()->create([
        'model_type' => 'product',
    ]);

    $attribute->delete();

    assertDatabaseMissing(Attribute::class, [
        'id' => $attribute->id,
    ]);

    assertDatabaseMissing('lunar_attribute_models', [
        'attribute_id' => $attribute->id,
    ]);
});
