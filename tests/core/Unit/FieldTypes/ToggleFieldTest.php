<?php

use Lunar\Exceptions\FieldTypeException;
use Lunar\FieldTypes\Toggle;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);

test('can set value', function () {
    $field = new Toggle;
    $field->setValue(false);

    expect($field->getValue())->toEqual(false);
});

test('can set value in constructor', function () {
    $field = new Toggle(true);

    expect($field->getValue())->toEqual(true);
});

test('check it does not allow array', function () {
    $this->expectException(FieldTypeException::class);

    new Toggle(['foo']);
});
