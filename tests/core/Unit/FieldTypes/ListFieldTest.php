<?php

use Lunar\Exceptions\FieldTypeException;
use Lunar\FieldTypes\ListField;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);

test('can set value', function () {
    $field = new ListField;
    $field->setValue([
        'Foo',
    ]);

    expect($field->getValue())->toEqual(['Foo']);
});

test('can set value in constructor', function () {
    $field = new ListField([
        'Foo',
    ]);

    expect($field->getValue())->toEqual(['Foo']);
});

test('can return keyed values as an array', function () {
    $field = new ListField([
        'card' => 'contact_card',
        'tag' => 'luggage_tag',
    ]);

    expect($field->getValue())->toEqual([
        'card' => 'contact_card',
        'tag' => 'luggage_tag',
    ]);
});

test('check does not allow non arrays', function () {
    $this->expectException(FieldTypeException::class);

    new ListField('Not an array');
});

test('toArray returns associative array for keyed values', function () {
    $field = new ListField([
        'foo' => 'bar',
        'baz' => 'qux',
    ]);

    expect($field->toArray())->toBe([
        'foo' => 'bar',
        'baz' => 'qux',
    ]);
});
