<?php

uses(\Lunar\Tests\Core\TestCase::class);
use Lunar\Exceptions\FieldTypeException;
use Lunar\FieldTypes\Text;

test('can set value', function () {
    $field = new Text();
    $field->setValue('I like cake');

    expect($field->getValue())->toEqual('I like cake');

    $field = new Text();
    $field->setValue(12345);

    expect($field->getValue())->toEqual(12345);

    $field = new Text();
    $field->setValue(true);

    expect($field->getValue())->toEqual(true);
});

test('can set null value', function () {
    $field = new Text();
    $field->setValue(null);

    expect($field->getValue())->toEqual(null);
});

test('can set value in constructor', function () {
    $field = new Text('I like cake');

    expect($field->getValue())->toEqual('I like cake');
});

test('check does not allow non strings', function () {
    $this->expectException(FieldTypeException::class);

    $field = new Text(new \stdClass());
});
