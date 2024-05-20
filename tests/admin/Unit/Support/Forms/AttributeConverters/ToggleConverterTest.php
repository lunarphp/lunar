<?php

uses(\Lunar\Tests\Admin\Unit\Livewire\TestCase::class)
    ->group('livewire.support.forms');

describe('toggle field converter', function () {
    beforeEach(function () {
        $this->asStaff();
    });

    test('can convert attribute to form input component', function () {
        $attribute = \Lunar\Models\Attribute::factory()->create([
            'type' => \Lunar\FieldTypes\Toggle::class,
        ]);

        $inputComponent = \Lunar\Admin\Support\FieldTypes\Toggle::getFilamentComponent($attribute);

        expect($inputComponent)->toBeInstanceOf(\Filament\Forms\Components\Toggle::class);
    });
});
