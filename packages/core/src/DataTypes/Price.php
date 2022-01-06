<?php

namespace GetCandy\DataTypes;

use GetCandy\Exceptions\InvalidDataTypeValueException;
use GetCandy\Models\Currency;

class Price
{
    /**
     * Initialise the Price datatype.
     *
     * @param  mixed  $value
     * @param  Currency  $currency
     * @param  int  $unitQty
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
     * @return string
     */
    protected function decimal()
    {
        return round(
            ($this->value / $this->currency->factor),
            $this->currency->decimal_places
        );
    }

    /**
     * Format the value with the currency.
     *
     * @return string
     */
    public function formatted()
    {
        $format = number_format(
            $this->decimal(),
            $this->currency->decimal_places,
            $this->currency->decimal_point,
            $this->currency->thousand_point
        );

        return str_replace('{value}', $format, $this->currency->format);
    }
}
