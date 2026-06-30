<?php

namespace Lunar\Core\Pricing;

use Lunar\Core\Models\Currency;

/**
 * Every operation takes a {@see Currency}, even when the default
 * implementation doesn't read from it. This is the seam consumers
 * subclass to apply currency-aware behaviour without changing the
 * interface shape — e.g. banker's rounding only for JPY, sub-minor
 * precision for currencies with `decimal_places >= 4`, or
 * jurisdiction-specific tax rounding rules.
 */
interface PriceCalculatorInterface
{
    /**
     * Multiply a minor-unit integer by a rate (e.g. 0.20 for 20%) and
     * round to whole minor units. Used for tax and percentage-based
     * discounts.
     */
    public function percentage(int $value, float $rate, Currency $currency): int;

    /**
     * Add tax to a tax-exclusive minor-unit value. `$rate` is a decimal
     * (0.20 = 20%).
     */
    public function withTax(int $value, float $rate, Currency $currency): int;

    /**
     * Strip tax from a tax-inclusive minor-unit value. Inverse of
     * {@see withTax()} to the minor unit:
     * `withTax(withoutTax($v, $r), $r) === $v` is a guarantee.
     */
    public function withoutTax(int $value, float $rate, Currency $currency): int;

    /**
     * Distribute a total across N weighted parts so the parts sum back
     * to the total exactly (no rounding drift). Used for fixed-value
     * discount allocation and multi-rate tax breakdown balancing.
     *
     * Keys are preserved from `$weights` in the returned array.
     *
     * @param  array<int|string, int>  $weights  non-negative integer weights
     * @return array<int|string, int> values that sum to `$total`
     */
    public function distribute(int $total, array $weights, Currency $currency): array;

    /**
     * Convert a major-unit decimal (e.g. `12.99`) to minor units using
     * the currency's factor, rounding to whole minor units.
     */
    public function toMinor(int|float|string $major, Currency $currency): int;

    /**
     * Convert a minor-unit integer to a major-unit decimal.
     */
    public function toMajor(int $minor, Currency $currency): float;
}
