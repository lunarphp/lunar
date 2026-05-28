<?php

namespace Lunar\Core\FieldTypes;

class File extends AbstractFieldType
{
    public function __toString(): string
    {
        if (is_array($this->value)) {
            return implode(', ', $this->value);
        }

        return (string) ($this->value ?? '');
    }

    public function setValue(mixed $value): void
    {
        $this->value = blank($value) ? null : $value;
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
                'disk' => 'string',
                'directory' => 'string',
            ],
        ];
    }
}
