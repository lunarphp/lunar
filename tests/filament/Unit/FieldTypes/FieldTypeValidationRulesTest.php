<?php

use Filament\Forms\Components\Field;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Lunar\Core\Enums\FieldTypeEnum;
use Lunar\Core\Models\Attribute;
use Lunar\Core\Models\Language;
use Lunar\Filament\FieldTypes\Dropdown;
use Lunar\Filament\FieldTypes\File;
use Lunar\Filament\FieldTypes\ListField;
use Lunar\Filament\FieldTypes\Number;
use Lunar\Filament\FieldTypes\TextField;
use Lunar\Filament\FieldTypes\Toggle;
use Lunar\Filament\FieldTypes\TranslatedText;
use Lunar\Filament\FieldTypes\Vimeo;
use Lunar\Filament\FieldTypes\YouTube;
use Lunar\Tests\Filament\TestCase;

uses(TestCase::class);

beforeEach(function () {
    Language::factory()->create(['default' => true]);
});

/**
 * Every converter paired with the attribute type it renders (spec 0062).
 */
dataset('field type converters', [
    'text' => [TextField::class, FieldTypeEnum::Text->value],
    'translated_text' => [TranslatedText::class, FieldTypeEnum::TranslatedText->value],
    'number' => [Number::class, FieldTypeEnum::Number->value],
    'dropdown' => [Dropdown::class, FieldTypeEnum::Dropdown->value],
    'file' => [File::class, FieldTypeEnum::File->value],
    'list' => [ListField::class, FieldTypeEnum::ListField->value],
    'toggle' => [Toggle::class, FieldTypeEnum::Toggle->value],
    'youtube' => [YouTube::class, FieldTypeEnum::YouTube->value],
    'vimeo' => [Vimeo::class, FieldTypeEnum::Vimeo->value],
]);

/**
 * As above, minus `file`: FileUpload::getValidationRules() folds non-array
 * rules into a per-file validator closure instead of returning them, so the
 * rules apply but are not introspectable at the top level.
 */
dataset('field type converters with introspectable rules', [
    'text' => [TextField::class, FieldTypeEnum::Text->value],
    'translated_text' => [TranslatedText::class, FieldTypeEnum::TranslatedText->value],
    'number' => [Number::class, FieldTypeEnum::Number->value],
    'dropdown' => [Dropdown::class, FieldTypeEnum::Dropdown->value],
    'list' => [ListField::class, FieldTypeEnum::ListField->value],
    'toggle' => [Toggle::class, FieldTypeEnum::Toggle->value],
    'youtube' => [YouTube::class, FieldTypeEnum::YouTube->value],
    'vimeo' => [Vimeo::class, FieldTypeEnum::Vimeo->value],
]);

function builtFieldFor(string $converter, Attribute $attribute): Field
{
    // getValidationRules() resolves utilities through the schema's Livewire
    // owner, so bind a minimal one.
    $livewire = new class extends \Livewire\Component implements \Filament\Schemas\Contracts\HasSchemas
    {
        use \Filament\Schemas\Concerns\InteractsWithSchemas;
    };

    return Schema::make($livewire)
        ->components([$converter::getFilamentComponent($attribute)])
        ->getComponents()[0];
}

it('applies the attribute validation rules to the built component', function (string $converter, string $type) {
    $attribute = Attribute::factory()
        ->validationRules(['min:2', 'max:10'])
        ->create(['type' => $type]);

    expect(builtFieldFor($converter, $attribute)->getValidationRules())
        ->toContain('min:2', 'max:10');
})->with('field type converters with introspectable rules');

it('builds components without rules under strict attribute access', function (string $converter, string $type) {
    // Regression for #2568: the converters used to read validation_rules as a
    // missing model attribute, which throws under strict mode.
    Model::preventAccessingMissingAttributes();

    try {
        $attribute = Attribute::factory()->create(['type' => $type]);

        // Refetch so wasRecentlyCreated no longer suppresses the exception,
        // matching what happens when a form loads a persisted record.
        $attribute = Attribute::query()->findOrFail($attribute->getKey());

        $rules = builtFieldFor($converter, $attribute)->getValidationRules();

        expect($rules)->not->toContain('min:2', 'max:10');
    } finally {
        Model::preventAccessingMissingAttributes(false);
    }
})->with('field type converters');
