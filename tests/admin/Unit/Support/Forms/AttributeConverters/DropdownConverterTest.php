<?php

use Filament\Forms\Components\Select;
use Lunar\Core\FieldTypes\Dropdown;
use Lunar\Core\Models\Attribute;
use Lunar\Tests\Admin\Unit\Livewire\TestCase;

uses(TestCase::class)
    ->group('livewire.support.forms');

describe('dropdown converter', function () {
    beforeEach(function () {
        $this->asStaff();
    });

    test('can convert attribute to form input component', function () {
        $attribute = Attribute::factory()->create([
            'type' => Dropdown::class,
        ]);

        $inputComponent = Lunar\Filament\FieldTypes\Dropdown::getFilamentComponent($attribute);

        expect($inputComponent)->toBeInstanceOf(Select::class);
    });

    test('can render dropdown options', function () {
        $attribute = Attribute::factory()->create([
            'type' => Dropdown::class,
            'configuration' => [
                'lookups' => [
                    [
                        'label' => 'Foo',
                        'value' => 'bar',
                    ],
                ],
            ],
        ]);

        $inputComponent = Lunar\Filament\FieldTypes\Dropdown::getFilamentComponent($attribute);

        $options = $inputComponent->getOptions();
        expect($options)->toBeArray()
            ->toHaveKey('bar')
            ->toContain('Foo');
    });

    test('can normalize dropdown lookups for edit forms', function () {
        $configuration = Lunar\Filament\FieldTypes\Dropdown::mutateConfigurationForForm([
            'lookups' => [
                [
                    'label' => 'Foo',
                    'value' => 'bar',
                ],
                'Baz' => 'qux',
            ],
        ]);

        expect($configuration)->toBe([
            'lookups' => [
                'Foo' => 'bar',
                'Baz' => 'qux',
            ],
        ]);
    });
});
