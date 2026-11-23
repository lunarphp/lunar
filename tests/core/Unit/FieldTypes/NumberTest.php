<?php

use Lunar\Core\Exceptions\FieldTypeException;
use Lunar\Core\FieldTypes\Number;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);

test('can set value', function () {
    $field = new Number;
    $field->setValue(12345);

    expect($field->getValue())->toEqual(12345);
});

test('can set value in constructor', function () {
    $field = new Number(12345);

    expect($field->getValue())->toEqual(12345);
});

test('can set as empty string', function () {
    $field = new Number('');

    expect($field->getValue())->toBeNull();

    $field->setValue('');

    expect($field->getValue())->toBeNull();
});

test('can set as null value', function () {
    $field = new Number(null);

    expect($field->getValue())->toBeNull()
        ->and($field->jsonSerialize())->toBeNull();

    $field->setValue(null);

    expect($field->getValue())->toBeNull();
});

test('keeps zero distinct from blank', function () {
    expect((new Number(0))->getValue())->toBe(0)
        ->and((new Number('0'))->getValue())->toBe(0)
        ->and((new Number('2.50'))->getValue())->toBe(2.5);
});

test('check does not allow non numerics', function () {
    $this->expectException(FieldTypeException::class);

    $field = new Number('bad string value');
});
