<?php

use Lunar\Core\DataObjects\PriceValue;
use Lunar\Core\Exceptions\MismatchedCurrencyException;
use Lunar\Core\Models\Currency;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);

function priceValueCurrency(string $code = 'TST'): Currency
{
    $ids = ['TST' => 1, 'GBP' => 2, 'EUR' => 3, 'USD' => 4];

    $currency = new Currency;
    $currency->code = $code;
    $currency->name = $code;
    $currency->decimal_places = 2;
    $currency->factor = 100;
    $currency->exchange_rate = 1;
    $currency->enabled = true;
    $currency->default = $code === 'TST';
    $currency->id = $ids[$code] ?? crc32($code);
    $currency->exists = true;

    return $currency;
}

test('add sums two values in the same currency', function () {
    $currency = priceValueCurrency();

    $sum = (new PriceValue(150, $currency))->add(new PriceValue(250, $currency));

    expect($sum)->toBeInstanceOf(PriceValue::class);
    expect($sum->value)->toBe(400);
    expect($sum->resolveCurrency()->code)->toBe('TST');
});

test('add throws on currency mismatch', function () {
    $a = new PriceValue(100, priceValueCurrency('GBP'));
    $b = new PriceValue(100, priceValueCurrency('EUR'));

    $a->add($b);
})->throws(MismatchedCurrencyException::class);

test('subtract returns the difference and may be negative', function () {
    $currency = priceValueCurrency();

    $result = (new PriceValue(100, $currency))->subtract(new PriceValue(150, $currency));

    expect($result->value)->toBe(-50);
    expect($result->isNegative())->toBeTrue();
});

test('subtract throws on currency mismatch', function () {
    $a = new PriceValue(500, priceValueCurrency('GBP'));
    $b = new PriceValue(100, priceValueCurrency('EUR'));

    $a->subtract($b);
})->throws(MismatchedCurrencyException::class);

test('multiply scales by an integer', function () {
    $currency = priceValueCurrency();

    $result = (new PriceValue(199, $currency))->multiply(3);

    expect($result->value)->toBe(597);
});

test('clampToZero floors negatives at zero', function () {
    $currency = priceValueCurrency();

    expect((new PriceValue(-50, $currency))->clampToZero()->value)->toBe(0);
    expect((new PriceValue(75, $currency))->clampToZero()->value)->toBe(75);
});

test('equals compares value and currency', function () {
    $gbp = priceValueCurrency('GBP');
    $eur = priceValueCurrency('EUR');

    expect((new PriceValue(100, $gbp))->equals(new PriceValue(100, $gbp)))->toBeTrue();
    expect((new PriceValue(100, $gbp))->equals(new PriceValue(200, $gbp)))->toBeFalse();
    expect((new PriceValue(100, $gbp))->equals(new PriceValue(100, $eur)))->toBeFalse();
});

test('isZero and isNegative report sign', function () {
    $currency = priceValueCurrency();

    expect((new PriceValue(0, $currency))->isZero())->toBeTrue();
    expect((new PriceValue(1, $currency))->isZero())->toBeFalse();
    expect((new PriceValue(-1, $currency))->isNegative())->toBeTrue();
    expect((new PriceValue(0, $currency))->isNegative())->toBeFalse();
});

test('sum aggregates an iterable of PriceValues', function () {
    $currency = priceValueCurrency();

    $sum = PriceValue::sum([
        new PriceValue(100, $currency),
        new PriceValue(250, $currency),
        new PriceValue(50, $currency),
    ], $currency);

    expect($sum->value)->toBe(400);
});

test('sum returns zero in the given currency for an empty iterable', function () {
    $currency = priceValueCurrency();

    $sum = PriceValue::sum([], $currency);

    expect($sum->value)->toBe(0);
    expect($sum->resolveCurrency()->code)->toBe('TST');
});

test('sum throws on currency mismatch within the iterable', function () {
    $gbp = priceValueCurrency('GBP');
    $eur = priceValueCurrency('EUR');

    PriceValue::sum([
        new PriceValue(100, $gbp),
        new PriceValue(50, $eur),
    ], $gbp);
})->throws(MismatchedCurrencyException::class);
