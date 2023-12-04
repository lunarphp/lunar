<?php

uses(\Lunar\Admin\Tests\Unit\Livewire\TestCase::class)
    ->group('livewire.support.forms');

describe('list field converter', function () {
    beforeEach(function () {
        $this->asStaff();
    });

    test('can convert attribute to form input component', function () {
        $attribute = \Lunar\Models\Attribute::factory()->create([
            'type' => \Lunar\FieldTypes\Number::class,
        ]);

        $inputComponent = \Lunar\Admin\Support\FieldTypes\Number::getFilamentComponent($attribute);

        expect($inputComponent)->toBeInstanceOf(\Filament\Forms\Components\TextInput::class);
        expect($inputComponent->isNumeric())->toBeTrue();
    });
});
