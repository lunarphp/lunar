<?php

uses(\Lunar\Tests\Admin\Unit\Livewire\TestCase::class)
    ->group('livewire.support');

describe('attribute data test', function () {
    beforeEach(function () {
        $this->asStaff();
    });

    test('correct form components are returned', function ($fieldType, $expectedComponent, $configuration = []) {
        $attribute = \Lunar\Models\Attribute::factory()->create([
            'type' => $fieldType,
            'configuration' => $configuration,
        ]);

        $inputComponent = \Lunar\Admin\Support\Facades\AttributeData::getFilamentComponent($attribute);

        expect($inputComponent)->toBeInstanceOf($expectedComponent);

    })->with([
        [\Lunar\FieldTypes\Text::class, \Filament\Forms\Components\TextInput::class],
        [\Lunar\FieldTypes\Text::class, \Filament\Forms\Components\RichEditor::class, ['richtext' => true]],
        [\Lunar\FieldTypes\Dropdown::class, \Filament\Forms\Components\Select::class],
        [\Lunar\FieldTypes\ListField::class, \Filament\Forms\Components\KeyValue::class],
        [\Lunar\FieldTypes\YouTube::class, \Lunar\Admin\Support\Forms\Components\YouTube::class],
        [\Lunar\FieldTypes\Number::class, \Filament\Forms\Components\TextInput::class],
    ]);

    test('can extend converters', function () {
        $attribute = \Lunar\Models\Attribute::factory()->create([
            'type' => TestFieldType::class,
        ]);

        \Lunar\Admin\Support\Facades\AttributeData::registerFieldType(TestFieldType::class, TestFieldConverter::class);

        $inputComponent = \Lunar\Admin\Support\Facades\AttributeData::getFilamentComponent($attribute);
        expect($inputComponent)->toBeInstanceOf(\Filament\Forms\Components\RichEditor::class);
    });
});

class TestFieldType extends Lunar\FieldTypes\Text {}

class TestFieldConverter extends \Lunar\Admin\Support\FieldTypes\TextField
{
    public static function getFilamentComponent(Lunar\Models\Attribute $attribute): Filament\Forms\Components\Component
    {
        return \Filament\Forms\Components\RichEditor::make($attribute->handle);
    }
}
