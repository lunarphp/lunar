<?php

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Lunar\Core\FieldTypes\Text;
use Lunar\Core\Models\Attribute;
use Lunar\Filament\FieldTypes\TextField;
use Lunar\Tests\Admin\Unit\Livewire\TestCase;

uses(TestCase::class)
    ->group('livewire.support.forms');

describe('list field converter', function () {
    beforeEach(function () {
        $this->asStaff();
    });

    test('can convert attribute to form input component', function () {
        $attribute = Attribute::factory()->create([
            'type' => Text::class,
        ]);

        $inputComponent = TextField::getFilamentComponent($attribute);

        expect($inputComponent)->toBeInstanceOf(TextInput::class);
    });

    test('can return richtext component', function () {
        $attribute = Attribute::factory()->create([
            'type' => Text::class,
            'configuration' => [
                'richtext' => true,
            ],
        ]);

        $inputComponent = TextField::getFilamentComponent($attribute);

        expect($inputComponent)->toBeInstanceOf(RichEditor::class);
    });
});
