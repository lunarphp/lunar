<?php

uses(\Lunar\Tests\TestCase::class);
use Lunar\Exceptions\FieldTypeException;
use Lunar\FieldTypes\Number;

test('can set value', function () {
    $field = new Number();
    $field->setValue(12345);

    expect($field->getValue())->toEqual(12345);
});

test('can set value in constructor', function () {
    $field = new Number(12345);

    expect($field->getValue())->toEqual(12345);
});

test('can set as empty string', function () {
    $field = new Number('');

    expect($field->getValue())->toEqual('');

    $field->setValue('');

    expect($field->getValue())->toEqual('');
});

test('can set as null value', function () {
    $field = new Number(null);

    expect($field->getValue())->toEqual(null);

    $field->setValue(null);

    expect($field->getValue())->toEqual(null);
});

test('check does not allow non numerics', function () {
    $this->expectException(FieldTypeException::class);

    $field = new Number('bad string value');
});
