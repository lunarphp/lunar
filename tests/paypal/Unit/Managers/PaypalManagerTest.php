<?php

use Lunar\Core\Models\Currency;
use Lunar\Paypal\Managers\PaypalManager;
use Lunar\Tests\Paypal\Unit\TestCase;

uses(TestCase::class);

function currency(string $code, int $decimalPlaces = 2): Currency
{
    return Currency::factory()->make([
        'code' => $code,
        'decimal_places' => $decimalPlaces,
    ]);
}

it('formats a lunar value as a paypal amount string', function (string $code, int $decimals, int $value, string $expected) {
    expect(PaypalManager::toPaypalAmount($value, currency($code, $decimals)))->toEqual($expected);
})->with([
    'two-decimal currency' => ['GBP', 2, 1999, '19.99'],
    'value under one unit' => ['GBP', 2, 29, '0.29'],
    'whole units' => ['GBP', 2, 500, '5.00'],
    'zero' => ['GBP', 2, 0, '0.00'],
    // PayPal rejects decimals on these, so the sub-unit scale collapses to whole units.
    'zero-decimal currency' => ['JPY', 0, 1999, '1999'],
    'zero-decimal currency, forint' => ['HUF', 0, 4500, '4500'],
    // Lunar stores KWD at 3dp; PayPal only accepts 2, so the value is rescaled.
    'three-decimal currency down to two' => ['KWD', 3, 19995, '20.00'],
    'three-decimal currency rounding down' => ['KWD', 3, 19994, '19.99'],
]);

it('parses a paypal amount string back to a lunar value', function (string $code, int $decimals, string $amount, int $expected) {
    expect(PaypalManager::fromPaypalAmount($amount, currency($code, $decimals)))->toEqual($expected);
})->with([
    // The float-multiply bug this replaces produced 1998 for this input.
    'the classic truncation case' => ['GBP', 2, '19.99', 1999],
    'another truncation case' => ['GBP', 2, '1.15', 115],
    'sub-unit truncation case' => ['GBP', 2, '0.29', 29],
    'whole units' => ['GBP', 2, '5.00', 500],
    'no decimal point' => ['GBP', 2, '5', 500],
    'zero' => ['GBP', 2, '0.00', 0],
    'zero-decimal currency' => ['JPY', 0, '1999', 1999],
    'three-decimal currency up from two' => ['KWD', 3, '19.99', 19990],
]);

it('round-trips every amount it formats', function (int $value, string $code, int $decimals) {
    $currency = currency($code, $decimals);

    expect(PaypalManager::fromPaypalAmount(
        PaypalManager::toPaypalAmount($value, $currency),
        $currency
    ))->toEqual($value);
})->with([
    [1999, 'GBP', 2],
    [29, 'GBP', 2],
    [115, 'GBP', 2],
    [100000, 'GBP', 2],
    [1999, 'JPY', 0],
]);

it('never loses a minor unit across the whole sub-unit range', function () {
    $currency = currency('GBP', 2);

    foreach (range(0, 999) as $value) {
        expect(PaypalManager::fromPaypalAmount(
            PaypalManager::toPaypalAmount($value, $currency),
            $currency
        ))->toEqual($value);
    }
});
