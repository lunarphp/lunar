<?php

namespace Lunar\Core\FieldTypes;

use Lunar\Core\Exceptions\FieldTypeException;

class Number extends AbstractFieldType
{
    public function setValue(mixed $value): void
    {
        if (! blank($value) && ! is_numeric($value)) {
            throw new FieldTypeException(self::class.' value must be numeric.');
        }

        // A blank value stays null so "not set" is distinct from zero. Otherwise
        // cast to int if the value is a whole number, or float if it is not.
        $this->value = blank($value) ? null : (floor($value) == $value ? (int) $value : (float) $value);
    }

    /**
     * {@inheritDoc}
     */
    public function getConfig(): array
    {
        return [
            'options' => [
                'min' => 'nullable|numeric',
                'max' => 'nullable|numeric',
            ],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getConfigurationFields(): array
    {
        return [
            ['key' => 'min', 'type' => 'number', 'label' => __('lunar::fieldtypes.min')],
            ['key' => 'max', 'type' => 'number', 'label' => __('lunar::fieldtypes.max')],
        ];
    }
}
