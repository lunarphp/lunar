<?php

use Illuminate\Database\Eloquent\Model;
use Lunar\Core\FieldTypes\Dropdown as DropdownFieldType;
use Lunar\Core\FieldTypes\File as FileFieldType;
use Lunar\Core\FieldTypes\ListField as ListFieldFieldType;
use Lunar\Core\FieldTypes\Number as NumberFieldType;
use Lunar\Core\FieldTypes\Text as TextFieldType;
use Lunar\Core\FieldTypes\Toggle as ToggleFieldType;
use Lunar\Core\FieldTypes\TranslatedText as TranslatedTextFieldType;
use Lunar\Core\FieldTypes\Vimeo as VimeoFieldType;
use Lunar\Core\FieldTypes\YouTube as YouTubeFieldType;
use Lunar\Core\Models\Attribute;
use Lunar\Filament\FieldTypes\Dropdown;
use Lunar\Filament\FieldTypes\File;
use Lunar\Filament\FieldTypes\ListField;
use Lunar\Filament\FieldTypes\Number;
use Lunar\Filament\FieldTypes\TextField;
use Lunar\Filament\FieldTypes\Toggle;
use Lunar\Filament\FieldTypes\TranslatedText;
use Lunar\Filament\FieldTypes\Vimeo;
use Lunar\Filament\FieldTypes\YouTube;
use Lunar\Tests\Admin\Unit\Livewire\TestCase;

uses(TestCase::class)
    ->group('livewire.support.forms');

describe('field type converters under strict attribute access', function () {
    beforeEach(function () {
        $this->asStaff();

        Model::preventAccessingMissingAttributes();
    });

    afterEach(function () {
        Model::preventAccessingMissingAttributes(false);
    });

    test('building a filament component does not read the removed validation_rules attribute', function (string $converter, string $type) {
        $attribute = Attribute::factory()->create([
            'type' => $type,
        ]);

        // Refetch to mimic opening an edit form, where wasRecentlyCreated is
        // false and missing-attribute access is no longer suppressed.
        $attribute = Attribute::findOrFail($attribute->getKey());

        $converter::getFilamentComponent($attribute);
    })->with([
        [TextField::class, TextFieldType::class],
        [TranslatedText::class, TranslatedTextFieldType::class],
        [Number::class, NumberFieldType::class],
        [Dropdown::class, DropdownFieldType::class],
        [File::class, FileFieldType::class],
        [ListField::class, ListFieldFieldType::class],
        [Toggle::class, ToggleFieldType::class],
        [YouTube::class, YouTubeFieldType::class],
        [Vimeo::class, VimeoFieldType::class],
    ])->throwsNoExceptions();
});
