<?php

uses(\Lunar\Tests\Admin\Unit\Livewire\TestCase::class)
    ->group('livewire.support.forms');

describe('dropdown converter', function () {
    beforeEach(function () {
        $this->asStaff();
    });

    test('can convert attribute to form input component', function () {
        $attribute = \Lunar\Models\Attribute::factory()->create([
            'type' => \Lunar\FieldTypes\Dropdown::class,
        ]);

        $inputComponent = \Lunar\Admin\Support\FieldTypes\Dropdown::getFilamentComponent($attribute);

        expect($inputComponent)->toBeInstanceOf(\Filament\Forms\Components\Select::class);
    });

    test('can render dropdown options', function () {
        $attribute = \Lunar\Models\Attribute::factory()->create([
            'type' => \Lunar\FieldTypes\Dropdown::class,
            'configuration' => [
                'lookups' => [
                    [
                        'label' => 'Foo',
                        'value' => 'bar',
                    ],
                ],
            ],
        ]);

        $inputComponent = \Lunar\Admin\Support\FieldTypes\Dropdown::getFilamentComponent($attribute);

        $options = $inputComponent->getOptions();
        expect($options)->toBeArray()
            ->toHaveKey('bar')
            ->toContain('Foo');
    });
});
