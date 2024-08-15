<?php

namespace Lunar\FieldTypes;

use JsonSerializable;
use Lunar\Base\FieldType;

class File implements FieldType, JsonSerializable
{
    /**
     * @var string|array|null
     */
    protected $value;

    /**
     * The config to use.
     */
    protected array $config = [];

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
     * Create a new instance of File field type.
     *
     * @param  string|array|null  $value
     */
    public function __construct($value = null)
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
        if (is_array($this->getValue())) {
            return implode(', ', $this->getValue());
        }

        return $this->getValue() ?? '';
    }

    /**
     * Return the value of this field.
     *
     * @return string|array|null
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set the value of this field.
     *
     * @param  string|array|null  $value
     */
    public function setValue($value)
    {
        if (blank($value)) {
            $value = null;
        }

        $this->value = $value;
    }

    /**
     * {@inheritDoc}
     */
    public function getConfig(): array
    {
        return [
            'options' => [
                'file_types' => 'array',
                'multiple' => 'boolean',
                'max_files' => 'numeric',
                'min_files' => 'numeric',
            ],
        ];
    }
}
