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
                'file_types' => 'nullable|array',
                'file_types.*' => 'string',
                'multiple' => 'nullable|boolean',
                'max_files' => 'nullable|numeric',
                'min_files' => 'nullable|numeric',
                'disk' => 'nullable|string',
                'directory' => 'nullable|string',
            ],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getConfigurationFields(): array
    {
        $disks = array_keys(config('filesystems.disks', []));

        return [
            [
                'key' => 'file_types',
                'type' => 'tags',
                'label' => __('lunar::fieldtypes.file_types'),
                'suggestions' => [
                    'image/*', 'image/jpeg', 'image/png', 'image/gif',
                    'audio/*', 'audio/mpeg', 'audio/aac', 'audio/wav',
                    'video/*', 'video/mp4', 'video/mpeg',
                    'application/msword',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'application/rtf', 'application/pdf',
                ],
            ],
            ['key' => 'multiple', 'type' => 'toggle', 'label' => __('lunar::fieldtypes.multiple')],
            ['key' => 'min_files', 'type' => 'number', 'label' => __('lunar::fieldtypes.min_files')],
            ['key' => 'max_files', 'type' => 'number', 'label' => __('lunar::fieldtypes.max_files')],
            [
                'key' => 'disk',
                'type' => 'select',
                'label' => __('lunar::fieldtypes.disk'),
                'options' => array_map(fn (string $disk) => ['label' => $disk, 'value' => $disk], $disks),
            ],
            ['key' => 'directory', 'type' => 'text', 'label' => __('lunar::fieldtypes.directory')],
        ];
    }
}
