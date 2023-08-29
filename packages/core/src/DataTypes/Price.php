<?php

namespace Lunar\DataTypes;

use Lunar\Exceptions\InvalidDataTypeValueException;
use Lunar\Models\Currency;
use Lunar\Pricing\DefaultPriceFormatter;
use NumberFormatter;

class Price
{
    /**
     * Initialise the Price datatype.
     *
     * @param  mixed  $value
     */
    public function __construct(
        public $value,
        public Currency $currency,
        public int $unitQty = 1
    ) {
        if (! is_int($value)) {
            throw new InvalidDataTypeValueException(
                'Value was "'.(gettype($value)).'" expected "int"'
            );
        }
        $this->value = $value;
        $this->currency = $currency;
        $this->unitQty = $unitQty;
    }

    /**
     * Getter for methods/properties.
     *
     * @param  string  $name
     * @return void
     */
    public function __get($name)
    {
        if (method_exists($this, $name)) {
            return $this->{$name}();
        }
    }

    /**
     * Cast class as a string.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->value;
    }

    private function formatter()
    {
        return app(
            config('lunar.pricing.formatter', DefaultPriceFormatter::class),
            [
                'value' => $this->value,
                'currency' => $this->currency,
                'unitQty' => $this->unitQty,
            ]
        );
    }

    /**
     * Get the decimal value.
     *
     * @return float
     */
    public function decimal($rounding = true)
    {
        return $this->formatter()->decimal($rounding);
    }

    /**
     * Get the decimal unit value.
     *
     * @return float
     */
    public function unitDecimal($rounding = true)
    {
        return $this->formatter()->unitDecimal($rounding);
    }

    /**
     * Format the value with the currency.
     *
     * @return string
     */
    public function formatted($locale = null, $formatterStyle = NumberFormatter::CURRENCY, $decimalPlaces = null, $trimTrailingZeros = true)
    {
        return $this->formatter()->formatted(
            locale: $locale,
            formatterStyle: $formatterStyle,
            decimalPlaces: $decimalPlaces,
            trimTrailingZeros: $trimTrailingZeros,
        );
    }

    /**
     * Format the unit value with the currency.
     *
     * @return string
     */
    public function unitFormatted($locale = null, $formatterStyle = NumberFormatter::CURRENCY, $decimalPlaces = null, $trimTrailingZeros = true)
    {
        return $this->formatter()->unitFormatted(
            locale: $locale,
            formatterStyle: $formatterStyle,
            decimalPlaces: $decimalPlaces,
            trimTrailingZeros: $trimTrailingZeros
        );
    }

    protected function formatValue($value, $locale = null, $formatterStyle = NumberFormatter::CURRENCY, $decimalPlaces = null, $trimTrailingZeros = true)
    {
        return $this->formatter()->formatValue(
            value: $value,
            locale: $locale,
            formatterStyle: $formatterStyle,
            decimalPlaces: $decimalPlaces,
            trimLeadingZeros: $trimTrailingZeros
        );
    }
}
