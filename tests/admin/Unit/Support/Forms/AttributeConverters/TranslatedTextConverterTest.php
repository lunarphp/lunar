<?php

use Lunar\Core\FieldTypes\TranslatedText;
use Lunar\Core\Models\Attribute;
use Lunar\Tests\Admin\Unit\Livewire\TestCase;

uses(TestCase::class)
    ->group('livewire.support.forms');

describe('list field converter', function () {
    beforeEach(function () {
        $this->asStaff();
    });

    test('can convert attribute to form input component', function () {
        $attribute = Attribute::factory()->create([
            'type' => TranslatedText::class,
        ]);

        $inputComponent = Lunar\Filament\FieldTypes\TranslatedText::getFilamentComponent($attribute);

        expect($inputComponent)->toBeInstanceOf(Lunar\Filament\Forms\Components\TranslatedText::class);
    });
});
