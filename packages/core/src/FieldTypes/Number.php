<?php

namespace Lunar\Core\FieldTypes;

use Lunar\Core\Exceptions\FieldTypeException;

class Number extends AbstractFieldType
{
    protected mixed $value = 0;

    public function setValue(mixed $value): void
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
