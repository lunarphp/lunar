<?php

use Lunar\FieldTypes\Toggle;
use Lunar\Models\Attribute;
use Lunar\Tests\Admin\Unit\Livewire\TestCase;

uses(TestCase::class)
    ->group('livewire.support.forms');

describe('toggle field converter', function () {
    beforeEach(function () {
        $this->asStaff();
    });

    test('can convert attribute to form input component', function () {
        $attribute = Attribute::factory()->create([
            'type' => Toggle::class,
        ]);

        $inputComponent = Lunar\Admin\Support\FieldTypes\Toggle::getFilamentComponent($attribute);

        expect($inputComponent)->toBeInstanceOf(Filament\Forms\Components\Toggle::class);
    });
});
