<?php

namespace Lunar\Paypal\Managers;

use Lunar\Core\Models\Currency;

class PaypalManager
{
    /**
     * Currencies PayPal does not accept decimal amounts for — the value must be
     * a whole number.
     *
     * @see https://developer.paypal.com/api/rest/reference/currency-codes/
     */
    protected const ZERO_DECIMAL_CURRENCIES = ['huf', 'jpy', 'twd'];

    /**
     * Convert a Lunar price value to the decimal string PayPal expects.
     *
     * Lunar stores prices as integers scaled by `Currency::decimal_places`,
     * which merchants set independently of what PayPal accepts for a given
     * currency. This rescales to PayPal's precision and formats the result,
     * so "1999" in a 2dp currency becomes "19.99" and "1999" in JPY becomes
     * "1999".
     */
    public static function toPaypalAmount(int $value, Currency $currency): string
    {
        $decimalPlaces = self::paypalDecimalPlaces($currency);

        $rescaled = self::rescale($value, max($currency->decimal_places, 0), $decimalPlaces);

        if ($decimalPlaces === 0) {
            return (string) $rescaled;
        }

        $negative = $rescaled < 0;
        $digits = str_pad((string) abs($rescaled), $decimalPlaces + 1, '0', STR_PAD_LEFT);

        return ($negative ? '-' : '')
            .substr($digits, 0, -$decimalPlaces)
            .'.'
            .substr($digits, -$decimalPlaces);
    }

    /**
     * Convert a decimal amount string received from PayPal back to a Lunar price
     * value scaled by `Currency::decimal_places`. Inverse of `toPaypalAmount()`.
     *
     * Parsing is done on the string rather than through a float — `(int) ("19.99"
     * * 100)` truncates to 1998 because 19.99 has no exact binary representation.
     */
    public static function fromPaypalAmount(string $amount, Currency $currency): int
    {
        $amount = trim($amount);
        $negative = str_starts_with($amount, '-');
        $amount = ltrim($amount, '+-');

        [$whole, $fraction] = array_pad(explode('.', $amount, 2), 2, '');

        $whole = $whole === '' ? '0' : $whole;
        $decimalPlaces = self::paypalDecimalPlaces($currency);

        // Keep one extra digit so a value carrying more precision than PayPal
        // uses for the currency still rounds rather than truncates.
        $fraction = substr(str_pad($fraction, $decimalPlaces + 1, '0'), 0, $decimalPlaces + 1);

        $value = self::rescale((int) ($whole.$fraction), $decimalPlaces + 1, $decimalPlaces);

        return self::rescale($value, $decimalPlaces, max($currency->decimal_places, 0)) * ($negative ? -1 : 1);
    }

    /**
     * The number of decimal places PayPal accepts amounts in for a currency.
     */
    protected static function paypalDecimalPlaces(Currency $currency): int
    {
        return in_array(strtolower($currency->code), self::ZERO_DECIMAL_CURRENCIES, true) ? 0 : 2;
    }

    /**
     * Rescale an integer amount between decimal-place precisions using integer
     * arithmetic only — float division misrounds at half-unit boundaries.
     * Rounds half away from zero, matching round().
     */
    protected static function rescale(int $value, int $fromDecimalPlaces, int $toDecimalPlaces): int
    {
        $exponent = $toDecimalPlaces - $fromDecimalPlaces;

        if ($exponent >= 0) {
            return $value * (10 ** $exponent);
        }

        $divisor = 10 ** (-$exponent);

        return intdiv(abs($value) + intdiv($divisor, 2), $divisor) * ($value < 0 ? -1 : 1);
    }
}
