<?php

namespace GetCandy\FieldTypes;

use GetCandy\Base\FieldType;
use GetCandy\Exceptions\FieldTypeException;
use JsonSerializable;

class Toggle implements FieldType, JsonSerializable
{
    /**
     * @var string
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
     * Create a new instance of Text field type.
     *
     * @param  string  $value
     */
    public function __construct($value = '')
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
        return $this->getValue() ?? '';
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
    public function getLabel(): string
    {
        return __('adminhub::fieldtypes.toggle.label');
    }

    /**
     * {@inheritDoc}
     */
    public function getSettingsView(): string
    {
        return 'adminhub::field-types.toggle.settings';
    }

    /**
     * {@inheritDoc}
     */
    public function getView(): string
    {
        return 'adminhub::field-types.toggle.view';
    }

    /**
     * {@inheritDoc}
     */
    public function getConfig(): array
    {
        return [
            'options' => [
                'on_value'  => 'nullable|string',
                'off_value' => 'nullable|string',
            ],
        ];
    }
}
