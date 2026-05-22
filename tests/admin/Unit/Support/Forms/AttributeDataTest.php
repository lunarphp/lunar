<?php

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Lunar\Admin\Support\Facades\AttributeData;
use Lunar\Core\FieldTypes\Dropdown;
use Lunar\Core\FieldTypes\ListField;
use Lunar\Core\FieldTypes\Number;
use Lunar\Core\FieldTypes\Text;
use Lunar\Core\FieldTypes\YouTube;
use Lunar\Core\Models\Attribute;
use Lunar\Filament\FieldTypes\TextField;
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
        [Text::class, TextInput::class],
        [Text::class, RichEditor::class, ['richtext' => true]],
        [Dropdown::class, Select::class],
        [ListField::class, KeyValue::class],
        [YouTube::class, Lunar\Filament\Forms\Components\YouTube::class],
        [Number::class, TextInput::class],
    ]);

    test('can extend converters', function () {
        $attribute = Attribute::factory()->create([
            'type' => TestFieldType::class,
        ]);

        AttributeData::registerFieldType(TestFieldType::class, TestFieldConverter::class);

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
