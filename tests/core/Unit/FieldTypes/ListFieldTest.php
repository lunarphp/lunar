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

test('check does not allow non arrays', function () {
    $this->expectException(FieldTypeException::class);

    new ListField('Not an array');
});
