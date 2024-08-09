<?php

uses(\Lunar\Tests\Core\TestCase::class);
use Illuminate\Support\Facades\Validator;
use Lunar\Rules\MaxDecimalPlaces;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('can validate decimal places using defaults', function () {
    $validator = Validator::make([
        'decimal' => 0.1,
    ], ['decimal' => new MaxDecimalPlaces]);

    expect($validator->passes())->toBeTrue();

    $validator = Validator::make([
        'decimal' => 0.12,
    ], ['decimal' => new MaxDecimalPlaces]);

    expect($validator->passes())->toBeTrue();

    $validator = Validator::make([
        'decimal' => 0.123,
    ], ['decimal' => new MaxDecimalPlaces]);

    expect($validator->fails())->toBeTrue();
});

test('can validate number with trailing zeros', function () {
    $validator = Validator::make([
        'decimal' => '164.6400',
    ], ['decimal' => new MaxDecimalPlaces(2)]);

    expect($validator->passes())->toBeTrue();
});

test('can validate long number', function () {
    $validator = Validator::make([
        'decimal' => 2000000000000000000,
    ], ['decimal' => new MaxDecimalPlaces(2)]);

    expect($validator->passes())->toBeTrue();
});

test('can validate on passed max decimals', function () {
    $validator = Validator::make([
        'decimal' => 0.1,
    ], ['decimal' => new MaxDecimalPlaces(3)]);

    expect($validator->passes())->toBeTrue();

    $validator = Validator::make([
        'decimal' => 0.12,
    ], ['decimal' => new MaxDecimalPlaces(3)]);

    expect($validator->passes())->toBeTrue();

    $validator = Validator::make([
        'decimal' => 0.123,
    ], ['decimal' => new MaxDecimalPlaces(3)]);

    expect($validator->passes())->toBeTrue();

    $validator = Validator::make([
        'decimal' => 0.1234,
    ], ['decimal' => new MaxDecimalPlaces(3)]);

    expect($validator->fails())->toBeTrue();
});

test('rule works on integers', function () {
    $validator = Validator::make([
        'decimal' => 1,
    ], ['decimal' => new MaxDecimalPlaces]);

    expect($validator->passes())->toBeTrue();

    $validator = Validator::make([
        'decimal' => 1,
    ], ['decimal' => new MaxDecimalPlaces(1)]);

    expect($validator->passes())->toBeTrue();

    $validator = Validator::make([
        'decimal' => 1,
    ], ['decimal' => new MaxDecimalPlaces(2)]);

    expect($validator->passes())->toBeTrue();

    $validator = Validator::make([
        'decimal' => 1,
    ], ['decimal' => new MaxDecimalPlaces(3)]);

    expect($validator->passes())->toBeTrue();
});
