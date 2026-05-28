<?php

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Lunar\Admin\Support\Facades\AttributeData;
use Lunar\Core\Enums\FieldTypeEnum;
use Lunar\Core\FieldTypes\Text;
use Lunar\Core\Models\Attribute;
use Lunar\Filament\FieldTypes\TextField;
use Lunar\Filament\Forms\Components\YouTube;
use Lunar\Tests\Admin\Unit\Livewire\TestCase;

uses(TestCase::class)
    ->group('livewire.support');

describe('attribute data test', function () {
    beforeEach(function () {
        $this->asStaff();
    });

    test('correct form components are returned', function ($fieldType, $expectedComponent, $configuration = []) {
        $attribute = Attribute::factory()->create([
            'type' => $fieldType,
            'configuration' => $configuration,
        ]);

        $inputComponent = AttributeData::getFilamentComponent($attribute);

        expect($inputComponent)->toBeInstanceOf($expectedComponent);

    })->with([
        [FieldTypeEnum::Text->value, TextInput::class],
        [FieldTypeEnum::Text->value, RichEditor::class, ['richtext' => true]],
        [FieldTypeEnum::Dropdown->value, Select::class],
        [FieldTypeEnum::ListField->value, KeyValue::class],
        [FieldTypeEnum::YouTube->value, YouTube::class],
        [FieldTypeEnum::Number->value, TextInput::class],
    ]);

    test('can extend converters', function () {
        $attribute = Attribute::factory()->create([
            'type' => 'test_field',
        ]);

        AttributeData::registerFieldType('test_field', TestFieldConverter::class);

        $inputComponent = AttributeData::getFilamentComponent($attribute);
        expect($inputComponent)->toBeInstanceOf(RichEditor::class);
    });
});

class TestFieldType extends Text {}

class TestFieldConverter extends TextField
{
    public static function getFilamentComponent(Attribute $attribute): Component
    {
        return RichEditor::make($attribute->handle);
    }
}
