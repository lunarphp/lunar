<?php

use Filament\Forms\Components\TextInput;
use Lunar\FieldTypes\Number;
use Lunar\Models\Attribute;
use Lunar\Tests\Admin\Unit\Livewire\TestCase;

uses(TestCase::class)
    ->group('livewire.support.forms');

describe('list field converter', function () {
    beforeEach(function () {
        $this->asStaff();
    });

    test('can convert attribute to form input component', function () {
        $attribute = Attribute::factory()->create([
            'type' => Number::class,
        ]);

        $inputComponent = Lunar\Admin\Support\FieldTypes\Number::getFilamentComponent($attribute);

        expect($inputComponent)->toBeInstanceOf(TextInput::class);
        expect($inputComponent->isNumeric())->toBeTrue();
    });
});
