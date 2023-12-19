<?php

uses(\Lunar\Tests\Admin\Unit\Livewire\TestCase::class)
    ->group('livewire.support.forms');

describe('list field converter', function () {
    beforeEach(function () {
        $this->asStaff();
    });

    test('can convert attribute to form input component', function () {
        $attribute = \Lunar\Models\Attribute::factory()->create([
            'type' => \Lunar\FieldTypes\Text::class,
        ]);

        $inputComponent = \Lunar\Admin\Support\FieldTypes\TextField::getFilamentComponent($attribute);

        expect($inputComponent)->toBeInstanceOf(\Filament\Forms\Components\TextInput::class);
    });

    test('can return richtext component', function () {
        $attribute = \Lunar\Models\Attribute::factory()->create([
            'type' => \Lunar\FieldTypes\Text::class,
            'configuration' => [
                'richtext' => true,
            ],
        ]);

        $inputComponent = \Lunar\Admin\Support\FieldTypes\TextField::getFilamentComponent($attribute);

        expect($inputComponent)->toBeInstanceOf(\Filament\Forms\Components\RichEditor::class);
    });
});
