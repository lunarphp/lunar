<?php

namespace Lunar\DataTypes;

use App;
use Lunar\Exceptions\InvalidDataTypeValueException;
use Lunar\Models\Currency;
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

    /**
     * Get the decimal value.
     *
     * @return float
     */
    public function decimal($rounding = true)
    {
        $convertedValue = $this->value / $this->currency->factor;

        return $rounding ? round($convertedValue, $this->currency->decimal_places) : $convertedValue;
    }

    /**
     * Get the decimal unit value.
     *
     * @return float
     */
    public function unitDecimal($rounding = true)
    {
        $convertedValue = $this->value / $this->currency->factor / $this->unitQty;

        return $rounding ? round($convertedValue, $this->currency->decimal_places) : $convertedValue;
    }

    /**
     * Format the value with the currency.
     *
     * @return string
     */
    public function formatted($locale = null, $formatterStyle = NumberFormatter::CURRENCY, $decimalPlaces = null)
    {
        return $this->formatValue($this->decimal(false), $locale, $formatterStyle, $decimalPlaces);
    }

    /**
     * Format the unit value with the currency.
     *
     * @return string
     */
    public function unitFormatted($locale = null, $formatterStyle = NumberFormatter::CURRENCY, $decimalPlaces = null)
    {
        return $this->formatValue($this->unitDecimal(false), $locale, $formatterStyle, $decimalPlaces);
    }

    protected function formatValue($value, $locale = null, $formatterStyle = NumberFormatter::CURRENCY, $decimalPlaces = null)
    {
        if (! $locale) {
            $locale = App::currentLocale();
        }

        $formatter = new NumberFormatter($locale, $formatterStyle);

        $formatter->setTextAttribute(NumberFormatter::CURRENCY_CODE, $this->currency->code);
        $formatter->setAttribute(NumberFormatter::FRACTION_DIGITS, $decimalPlaces ?? $this->currency->decimal_places);

        return $formatter->format($value);
    }
}
