<?php

namespace Lunar\FieldTypes;

use Lunar\Base\FieldType;
use Lunar\Exceptions\FieldTypeException;
use JsonSerializable;

class Dropdown implements FieldType, JsonSerializable
{
    /**
     * @var string|int
     */
    protected $value;

    /**
     * Create a new instance of List field type.
     *
     * @param  string|int  $value
     */
    public function __construct($value = '')
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
     * @return string|int
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set the value of this field.
     *
     * @param  string|int  $value
     */
    public function setValue($value)
    {
        if ($value && ! is_string($value)) {
            throw new FieldTypeException(self::class.' value must be a string.');
        }

        $this->value = $value;
    }

    /**
     * {@inheritDoc}
     */
    public function getLabel(): string
    {
        return __('adminhub::fieldtypes.dropdown.label');
    }

    /**
     * {@inheritDoc}
     */
    public function getSettingsView(): string
    {
        return 'adminhub::field-types.dropdown.settings';
    }

    /**
     * {@inheritDoc}
     */
    public function getView(): string
    {
        return 'adminhub::field-types.dropdown.view';
    }

    /**
     * {@inheritDoc}
     */
    public function getConfig(): array
    {
        return [
            'options' => [
                'lookups'         => 'array',
                'lookups.*.label' => 'string|required',
                'lookups.*.value' => 'nullable|string',
            ],
        ];
    }
}
