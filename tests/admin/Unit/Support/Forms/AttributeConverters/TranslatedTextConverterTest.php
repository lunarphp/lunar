<?php

use Lunar\FieldTypes\TranslatedText;
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
            'type' => TranslatedText::class,
        ]);

        $inputComponent = Lunar\Admin\Support\FieldTypes\TranslatedText::getFilamentComponent($attribute);

        expect($inputComponent)->toBeInstanceOf(Lunar\Admin\Support\Forms\Components\TranslatedText::class);
    });
});
