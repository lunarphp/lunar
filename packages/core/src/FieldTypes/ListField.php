<?php

namespace Lunar\Core\FieldTypes;

use Illuminate\Contracts\Support\Arrayable;
use Lunar\Core\Exceptions\FieldTypeException;

class ListField extends AbstractFieldType implements Arrayable
{
    protected mixed $value = [];

    public function setValue(mixed $value): void
    {
        if (blank($value)) {
            $this->value = [];

            return;
        }

        if (! is_array($value)) {
            throw new FieldTypeException(self::class.' value must be an array.');
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
                'max_items' => 'numeric|nullable',
            ],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getConfigurationFields(): array
    {
        return [
            ['key' => 'max_items', 'type' => 'number', 'label' => __('lunar::fieldtypes.max_items')],
        ];
    }

    /**
     * Return the value as an array (implements Arrayable for Filament 4 compatibility).
     *
     * @return array<int|string, mixed>
     */
    public function toArray(): array
    {
        return (array) $this->value;
    }
}
