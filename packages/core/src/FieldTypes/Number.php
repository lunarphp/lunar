<?php

namespace Lunar\FieldTypes;

use JsonSerializable;
use Lunar\Base\FieldType;
use Lunar\Exceptions\FieldTypeException;

class Number implements FieldType, JsonSerializable
{
    /**
     * @var int|float
     */
    protected $value;

    /**
     * Create a new instance of Number field type.
     *
     * @param  int|float  $value
     */
    public function __construct($value = 0)
    {
        $this->setValue($value);
    }

    /**
     * Serialize the class.
     *
     * @return string
     */
    public function jsonSerialize(): mixed
    {
        return $this->value;
    }

    /**
     * Return the value of this field.
     *
     * @return int|float
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set the value of this field.
     *
     * @param  string|int|float|null  $value
     */
    public function setValue($value)
    {
        if (! blank($value) && ! is_numeric($value)) {
            throw new FieldTypeException(self::class.' value must be numeric.');
        }

        // Cast to int if the value is a whole number, otherwise use float
        $this->value = blank($value) ? 0 : (floor($value) == $value ? (int) $value : (float) $value);
    }

    /**
     * {@inheritDoc}
     */
    public function getConfig(): array
    {
        return [
            'options' => [
                'min' => 'numeric|min:1',
                'max' => 'numeric',
            ],
        ];
    }
}
