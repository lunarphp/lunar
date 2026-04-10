<?php

use Filament\Forms\Components\KeyValue;
use Lunar\FieldTypes\ListField;
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
            'type' => ListField::class,
        ]);

        $inputComponent = Lunar\Admin\Support\FieldTypes\ListField::getFilamentComponent($attribute);

        expect($inputComponent)->toBeInstanceOf(KeyValue::class);
    });
});
