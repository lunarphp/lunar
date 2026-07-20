<?php

use Lunar\Core\FieldTypes\Dropdown;
use Lunar\Core\FieldTypes\File;
use Lunar\Core\FieldTypes\ListField;
use Lunar\Core\FieldTypes\Number;
use Lunar\Core\FieldTypes\Text;
use Lunar\Core\FieldTypes\Toggle;
use Lunar\Core\FieldTypes\TranslatedText;
use Lunar\Core\FieldTypes\Vimeo;
use Lunar\Core\FieldTypes\YouTube;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);

function descriptorKeys(array $fields): array
{
    return array_map(fn (array $field) => [$field['key'], $field['type']], $fields);
}

test('text and translated text declare a richtext toggle', function () {
    expect(descriptorKeys((new Text)->getConfigurationFields()))->toBe([['richtext', 'toggle']])
        ->and(descriptorKeys((new TranslatedText)->getConfigurationFields()))->toBe([['richtext', 'toggle']]);
});

test('number declares min and max inputs', function () {
    expect(descriptorKeys((new Number)->getConfigurationFields()))->toBe([['min', 'number'], ['max', 'number']]);
});

test('dropdown declares a lookups editor', function () {
    expect(descriptorKeys((new Dropdown)->getConfigurationFields()))->toBe([['lookups', 'lookups']]);
});

test('list declares a max items input', function () {
    expect(descriptorKeys((new ListField)->getConfigurationFields()))->toBe([['max_items', 'number']]);
});

test('file declares upload settings with disk options from the filesystems config', function () {
    config()->set('filesystems.disks', ['local' => [], 's3' => []]);

    $fields = (new File)->getConfigurationFields();

    expect(descriptorKeys($fields))->toBe([
        ['file_types', 'tags'],
        ['multiple', 'toggle'],
        ['min_files', 'number'],
        ['max_files', 'number'],
        ['disk', 'select'],
        ['directory', 'text'],
    ]);

    $disk = collect($fields)->firstWhere('key', 'disk');

    expect($disk['options'])->toBe([
        ['label' => 'local', 'value' => 'local'],
        ['label' => 's3', 'value' => 's3'],
    ]);

    $fileTypes = collect($fields)->firstWhere('key', 'file_types');

    expect($fileTypes['suggestions'])->toContain('image/*', 'application/pdf');
});

test('every descriptor carries a translated label', function () {
    $types = [new Text, new TranslatedText, new Number, new Dropdown, new ListField, new File];

    foreach ($types as $fieldType) {
        foreach ($fieldType->getConfigurationFields() as $field) {
            expect($field['label'])->toBeString()->not->toStartWith('lunar::');
        }
    }
});

test('field types without settings declare none', function () {
    expect((new Toggle)->getConfigurationFields())->toBe([])
        ->and((new Vimeo)->getConfigurationFields())->toBe([])
        ->and((new YouTube)->getConfigurationFields())->toBe([]);
});
