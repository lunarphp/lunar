<?php

namespace Lunar\DataTypes;

use Illuminate\Contracts\Support\Arrayable;
use Lunar\Exceptions\InvalidDataTypeValueException;
use Lunar\Models\Currency;
use Lunar\Pricing\DefaultPriceFormatter;

class Price implements Arrayable
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
     */
    public function decimal(...$arguments): float
    {
        return $this->formatter()->decimal(...$arguments);
    }

    /**
     * Get the decimal unit value.
     */
    public function unitDecimal(...$arguments): float
    {
        return $this->formatter()->unitDecimal(...$arguments);
    }

    /**
     * Format the value with the currency.
     *
     * @return string
     */
    public function formatted(...$arguments): mixed
    {
        return $this->formatter()->formatted(...$arguments);
    }

    /**
     * Format the unit value with the currency.
     *
     * @return string
     */
    public function unitFormatted(...$arguments): mixed
    {
        return $this->formatter()->unitFormatted(...$arguments);
    }

    protected function formatValue(int|float $value, ...$arguments): mixed
    {
        return $this->formatter()->formatValue($value, ...$arguments);
    }

    /**
     * Get the instance as an array.
     *
     * @return array<TKey, TValue>
     */
    public function toArray()
    {
        return [
            'value' => $this->value,
            'decimal' => $this->decimal,
            'formatted' => $this->formatted,
            'currency' => $this->currency->code,
        ];
    }
}
