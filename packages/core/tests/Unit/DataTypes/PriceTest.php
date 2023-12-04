<?php

uses(\Lunar\Tests\TestCase::class);
use Lunar\DataTypes\Price;
use Lunar\Exceptions\InvalidDataTypeValueException;
use Lunar\Models\Currency;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('can initiate the datatype', function () {
    $currency = Currency::factory()->create([
        'code' => 'GBP',
        'decimal_places' => 2,
    ]);

    $dataType = new Price(1500, $currency, 1);

    expect($dataType)->toBeInstanceOf(Price::class);

    expect($dataType->value)->toEqual(1500);
    expect($dataType->decimal)->toEqual(15.00);
    expect($dataType->formatted)->toEqual('£15.00');
});

test('can handle multiple decimal places', function () {
    $currency = Currency::factory()->create([
        'code' => 'GBP',
        'decimal_places' => 3,
    ]);

    $dataType = new Price(1500, $currency, 1);

    expect($dataType->value)->toEqual(1500);
    expect($dataType->decimal)->toEqual(1.500);
    expect($dataType->formatted)->toEqual('£1.500');

    $dataType = new Price(1155, $currency, 1);

    expect($dataType->value)->toEqual(1155);
    expect($dataType->decimal)->toEqual(1.155);
    expect($dataType->formatted)->toEqual('£1.155');
});

test('can handle unit qty', function () {
    $currency = Currency::factory()->create([
        'code' => 'GBP',
        'decimal_places' => 3,
    ]);

    $dataType = new Price(1155, $currency, 10);

    expect($dataType->value)->toEqual(1155);
    expect($dataType->decimal)->toEqual(1.155);
    expect($dataType->unitDecimal)->toEqual(0.116);
    expect($dataType->unitDecimal(false))->toEqual(0.1155);
    expect($dataType->formatted)->toEqual('£1.155');
    expect($dataType->unitFormatted)->toEqual('£0.116');
    expect($dataType->unitFormatted(null, NumberFormatter::CURRENCY, 4))->toEqual('£0.1155');
});

test('can handle no decimal places', function () {
    $currency = Currency::factory()->create([
        'code' => 'VND',
        'decimal_places' => 0,
    ]);

    $dataType = new Price(100, $currency, 1);

    expect($dataType->value)->toEqual(100);
    expect($dataType->decimal)->toEqual(100);
    expect($dataType->formatted)->toEqual('₫100');
});

test('can format numbers', function () {
    $currency = Currency::factory()->create([
        'code' => 'EUR',
        'decimal_places' => 2,
    ]);

    $dataType = new Price(1500, $currency, 1);

    expect($dataType->formatted('fr'))->toEqual('15,00 €');
    expect($dataType->formatted('en-gb'))->toEqual('€15.00');
    expect($dataType->formatted('en-gb', \NumberFormatter::SPELLOUT))->toEqual('fifteen');
});

test('can format numbers specifying decimal places', function () {
    $currency = Currency::factory()->create([
        'code' => 'USD',
        'decimal_places' => 2,
    ]);

    $dataType = new Price(1500, $currency, 1);
    expect($dataType->formatted(decimalPlaces: 6, trimTrailingZeros: true))->toEqual('$15.00');
    expect($dataType->formatted(decimalPlaces: 6, trimTrailingZeros: false))->toEqual('$15.000000');

    $dataType = new Price(507, $currency, 100);
    expect($dataType->unitFormatted(decimalPlaces: 6, trimTrailingZeros: true))->toEqual('$0.0507');
    expect($dataType->unitFormatted(decimalPlaces: 6, trimTrailingZeros: false))->toEqual('$0.050700');
});

test('can format numbers specifying decimal places with currency suffix', function () {
    $currency = Currency::factory()->create([
        'code' => 'SEK',
        'decimal_places' => 2,
    ]);

    $dataType = new Price(15000, $currency, 1);
    expect($dataType->formatted(locale: 'sv', decimalPlaces: 6, trimTrailingZeros: true))->toEqual('150,00 kr');
    expect($dataType->formatted(locale: 'sv', decimalPlaces: 6, trimTrailingZeros: false))->toEqual('150,000000 kr');

    $dataType = new Price(50050, $currency, 100);
    expect($dataType->unitFormatted(locale: 'sv', decimalPlaces: 6, trimTrailingZeros: true))->toEqual('5,005 kr');
    expect($dataType->unitFormatted(locale: 'sv', decimalPlaces: 6, trimTrailingZeros: false))->toEqual('5,005000 kr');
});

test('can handle decimals being passed', function () {
    $currency = Currency::factory()->create([
        'code' => 'GBP',
        'decimal_places' => 2,
    ]);

    $this->expectException(InvalidDataTypeValueException::class);

    new Price(15.99, $currency, 1);
});
