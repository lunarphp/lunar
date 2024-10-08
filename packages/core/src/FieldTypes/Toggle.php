<?php

namespace Lunar\FieldTypes;

use JsonSerializable;
use Lunar\Base\FieldType;
use Lunar\Exceptions\FieldTypeException;

class Toggle implements FieldType, JsonSerializable
{
    /**
     * @var bool
     */
    protected $value;

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
     * Create a new instance of Toggle field type.
     *
     * @param  boolean|string  $value
     */
    public function __construct($value = false)
    {
        $this->setValue($value);
    }

    /**
     * Returns the value when accessed as a string.
     *
     * @return string
     */
    public function __toString()
    {
        return (int) ($this->getValue() ?? false);
    }

    /**
     * Return the value of this field.
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set the value of this field.
     *
     * @param  string  $value
     */
    public function setValue($value)
    {
        if ($value && is_array($value)) {
            throw new FieldTypeException(self::class.' value must be a string or boolean.');
        }

        $this->value = $value ?: false;
    }

    /**
     * {@inheritDoc}
     */
    public function getConfig(): array
    {
        return [];
    }
}
