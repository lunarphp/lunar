<?php

use Lunar\Core\Models\Currency;
use Lunar\Core\Pricing\DefaultPriceCalculator;
use Lunar\Tests\Core\TestCase;

uses(TestCase::class);

function makeCurrency(int $decimals = 2): Currency
{
    $currency = new Currency;
    $currency->code = 'TST';
    $currency->name = 'Test';
    $currency->decimal_places = $decimals;
    $currency->factor = (int) (10 ** $decimals);
    $currency->exchange_rate = 1;
    $currency->enabled = true;
    $currency->default = true;

    return $currency;
}

test('percentage rounds half away from zero', function () {
    $calc = new DefaultPriceCalculator;
    $currency = makeCurrency();

    expect($calc->percentage(100, 0.20, $currency))->toBe(20);
    expect($calc->percentage(99, 0.20, $currency))->toBe(20); // 19.8 -> 20
    expect($calc->percentage(1, 0.5, $currency))->toBe(1);    // 0.5 -> 1
    expect($calc->percentage(0, 0.99, $currency))->toBe(0);
});

test('withTax applies the rate', function () {
    $calc = new DefaultPriceCalculator;
    $currency = makeCurrency();

    expect($calc->withTax(1000, 0.20, $currency))->toBe(1200);
    expect($calc->withTax(1000, 0.0, $currency))->toBe(1000);
    expect($calc->withTax(99, 0.175, $currency))->toBe(116); // 116.325 -> 116
});

test('withoutTax is a strict inverse of withTax for all rates', function () {
    $calc = new DefaultPriceCalculator;
    $currency = makeCurrency();

    $rates = [0.0, 0.05, 0.10, 0.175, 0.20, 0.2125, 0.25, 0.27];

    foreach ($rates as $rate) {
        for ($x = 0; $x < 500; $x++) {
            $withTax = $calc->withTax($x, $rate, $currency);
            $back = $calc->withoutTax($withTax, $rate, $currency);

            // The result is the largest x' where withTax(x') <= withTax(x),
            // so withTax(back) === withTax(x) is the strict round-trip.
            expect($calc->withTax($back, $rate, $currency))->toBe($withTax);
        }
    }
});

test('withoutTax then withTax recovers the original value', function () {
    $calc = new DefaultPriceCalculator;
    $currency = makeCurrency();

    foreach ([0.05, 0.10, 0.20, 0.175] as $rate) {
        for ($v = 0; $v < 500; $v++) {
            $withTax = $calc->withTax($v, $rate, $currency);
            expect($calc->withTax($calc->withoutTax($withTax, $rate, $currency), $rate, $currency))
                ->toBe($withTax);
        }
    }
});

test('distribute sums back to the total exactly', function () {
    $calc = new DefaultPriceCalculator;
    $currency = makeCurrency();

    $result = $calc->distribute(100, [1, 1, 1], $currency);
    expect(array_sum($result))->toBe(100);

    $result = $calc->distribute(1000, [33, 33, 34], $currency);
    expect(array_sum($result))->toBe(1000);

    $result = $calc->distribute(7, [1, 1, 1], $currency);
    expect(array_sum($result))->toBe(7);
});

test('distribute preserves keys and uses largest remainder', function () {
    $calc = new DefaultPriceCalculator;
    $currency = makeCurrency();

    $result = $calc->distribute(10, ['a' => 1, 'b' => 1, 'c' => 1], $currency);

    expect(array_keys($result))->toBe(['a', 'b', 'c']);
    expect(array_sum($result))->toBe(10);
    // Two lines get the +1, the third the floor.
    expect(array_values($result))->toContain(3, 4);
});

test('distribute zero-weight lines get zero', function () {
    $calc = new DefaultPriceCalculator;
    $currency = makeCurrency();

    $result = $calc->distribute(100, [50, 0, 50], $currency);

    expect($result)->toBe([0 => 50, 1 => 0, 2 => 50]);
});

test('distribute all-zero weights returns zeros', function () {
    $calc = new DefaultPriceCalculator;
    $currency = makeCurrency();

    expect($calc->distribute(100, [0, 0, 0], $currency))->toBe([0, 0, 0]);
});

test('distribute throws on negative weights', function () {
    $calc = new DefaultPriceCalculator;
    $currency = makeCurrency();

    expect(fn () => $calc->distribute(100, [50, -1, 50], $currency))
        ->toThrow(InvalidArgumentException::class);
});

test('distribute empty weights returns empty', function () {
    $calc = new DefaultPriceCalculator;
    $currency = makeCurrency();

    expect($calc->distribute(100, [], $currency))->toBe([]);
});

test('toMinor with 2 decimal places uses float math', function () {
    $calc = new DefaultPriceCalculator;
    $currency = makeCurrency(2);

    expect($calc->toMinor(12.99, $currency))->toBe(1299);
    expect($calc->toMinor('12.99', $currency))->toBe(1299);
    expect($calc->toMinor(0, $currency))->toBe(0);
});

test('toMinor with 3 decimal places', function () {
    $calc = new DefaultPriceCalculator;
    $currency = makeCurrency(3);

    expect($calc->toMinor(1.234, $currency))->toBe(1234);
    expect($calc->toMinor('1.234', $currency))->toBe(1234);
});

test('toMinor with 4 decimal places uses bc math', function () {
    $calc = new DefaultPriceCalculator;
    $currency = makeCurrency(4);

    expect($calc->toMinor('1.2345', $currency))->toBe(12345);
    expect($calc->toMinor('1.99995', $currency))->toBe(20000); // half-up
});

test('toMajor inverts toMinor', function () {
    $calc = new DefaultPriceCalculator;
    $currency = makeCurrency(2);

    expect($calc->toMajor(1299, $currency))->toBe(12.99);
});
