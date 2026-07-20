<?php

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Lunar\Core\Facades\FieldTypeManifest;
use Lunar\Core\FieldTypes\AbstractFieldType;
use Lunar\Filament\Support\Facades\AttributeData;
use Lunar\Filament\Support\Forms\ConfigurationFieldMapper;
use Lunar\Tests\Filament\TestCase;

uses(TestCase::class);

it('maps each descriptor type onto the matching Filament component', function () {
    $components = ConfigurationFieldMapper::map([
        ['key' => 'directory', 'type' => 'text', 'label' => 'Directory'],
        ['key' => 'min', 'type' => 'number', 'label' => 'Minimum value'],
        ['key' => 'richtext', 'type' => 'toggle', 'label' => 'Rich text', 'hint' => 'Use the editor.'],
        ['key' => 'disk', 'type' => 'select', 'label' => 'Disk', 'options' => [['label' => 'local', 'value' => 'local']]],
        ['key' => 'file_types', 'type' => 'tags', 'label' => 'File types', 'suggestions' => ['image/*']],
        ['key' => 'lookups', 'type' => 'lookups', 'label' => 'Options'],
    ]);

    expect($components)->toHaveCount(6)
        ->and($components[0])->toBeInstanceOf(TextInput::class)
        ->and($components[0]->getLabel())->toBe('Directory')
        ->and($components[1])->toBeInstanceOf(TextInput::class)
        ->and($components[2])->toBeInstanceOf(Toggle::class)
        ->and($components[3])->toBeInstanceOf(Select::class)
        ->and($components[4])->toBeInstanceOf(TagsInput::class)
        ->and($components[5])->toBeInstanceOf(KeyValue::class)
        ->and($components[5]->getName())->toBe('lookups');
});

it('skips unknown descriptor types for forward compatibility', function () {
    $components = ConfigurationFieldMapper::map([
        ['key' => 'palette', 'type' => 'colour-wheel', 'label' => 'Palette'],
        ['key' => 'min', 'type' => 'number', 'label' => 'Minimum value'],
    ]);

    expect($components)->toHaveCount(1)
        ->and($components[0])->toBeInstanceOf(TextInput::class);
});

it('derives configuration fields from core descriptors for built-in types', function () {
    $components = AttributeData::getConfigurationFields('number');

    expect($components)->toHaveCount(2)
        ->and($components[0])->toBeInstanceOf(TextInput::class)
        ->and($components[0]->getName())->toBe('min')
        ->and($components[1]->getName())->toBe('max');
});

it('gives a core-registered custom field type a config form with no bridge class', function () {
    FieldTypeManifest::add('stars', StarRatingConfigFieldType::class);

    $components = AttributeData::getConfigurationFields('stars');

    expect($components)->toHaveCount(1)
        ->and($components[0])->toBeInstanceOf(TextInput::class)
        ->and($components[0]->getName())->toBe('scale');
});

class StarRatingConfigFieldType extends AbstractFieldType
{
    public function getConfigurationFields(): array
    {
        return [
            ['key' => 'scale', 'type' => 'number', 'label' => 'Scale'],
        ];
    }
}
