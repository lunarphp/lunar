<?php

namespace Lunar\Core\FieldTypes;

use Lunar\Core\Exceptions\FieldTypeException;

class Dropdown extends AbstractFieldType
{
    public function setValue(mixed $value): void
    {
        if ($value && ! is_string($value)) {
            throw new FieldTypeException(self::class.' value must be a string.');
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
                'lookups' => 'array',
                'lookups.*.label' => 'string|required',
                'lookups.*.value' => 'nullable|string',
            ],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getConfigurationFields(): array
    {
        return [
            ['key' => 'lookups', 'type' => 'lookups', 'label' => __('lunar::fieldtypes.lookups')],
        ];
    }
}
