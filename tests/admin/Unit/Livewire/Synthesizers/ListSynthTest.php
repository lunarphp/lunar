<?php

use Lunar\Admin\Support\Synthesizers\ListSynth;
use Lunar\FieldTypes\ListField;

uses(\Lunar\Tests\Admin\Unit\Livewire\TestCase::class)
    ->group('support.synthesizers');

describe('list field synthesizer', function () {
    beforeEach(function () {
        $this->listSynth = Mockery::mock(ListSynth::class)->makePartial();
        $this->listField = Mockery::mock(ListField::class)->makePartial();
    });

    test('sets a value in the list field', function () {
        $key = 'item1';
        $value = 'Test Value';

        $this->listSynth->set($this->listField, $key, $value);

        $result = $this->listField->getValue();

        expect($result)->toBeObject()
            ->and($result)->toHaveKey($key, $value);
    });

    test('unsets a value from the list field', function () {
        $key = 'item1';
        $value = 'Test Value';

        $this->listField->setValue([$key => $value]);

        $this->listSynth->unset($this->listField, $key);

        $result = $this->listField->getValue();

        expect($result)->toBeArray()
            ->and($result)->not->toHaveKey($key);
    });

    test('gets values from the list field', function () {
        $key = 'item1';
        $value = 'Test Value';
        $this->listField->setValue([$key => $value]);

        $result = $this->listSynth->get($this->listField, $key);

        expect($result)->toBeArray()
            ->and($result)->toEqual((array) $this->listField->getValue());
    });

    test('dehydrates the list field correctly', function () {
        $this->listField->setValue(['item1' => 'Test Value']);

        $result = $this->listSynth->dehydrate($this->listField)[0];

        expect($result)->toEqual($this->listField->getValue());
    });

    test('handles empty keys and values', function () {
        $key = '';
        $value = '';

        $this->listSynth->set($this->listField, $key, $value);

        $result = $this->listField->getValue();

        expect($result)->toBeObject()
            ->and($result)->toHaveKey($key, $value);

        $this->listSynth->unset($this->listField, $key);

        $result = $this->listField->getValue();

        expect($result)->toBeArray()
            ->and($result)->not->toHaveKey($key);
    });

    test('handles keys and values with dot notation', function () {
        $key = 'key.with.dots';
        $value = 'Dot.Notation.Value';

        $this->listSynth->set($this->listField, $key, $value);

        $result = $this->listField->getValue();

        expect($result)->toBeObject()
            ->and($result)->toHaveKey($key, $value);

        $this->listSynth->unset($this->listField, $key);

        $result = $this->listField->getValue();

        expect($result)->toBeArray()
            ->and($result)->not->toHaveKey($key);
    });
});
