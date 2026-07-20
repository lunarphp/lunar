<?php

namespace Lunar\Core\FieldTypes;

use Lunar\Core\Exceptions\FieldTypeException;

class Text extends AbstractFieldType
{
    public function setValue(mixed $value): void
    {
        if ($value && (! is_string($value) && ! is_numeric($value) && ! is_bool($value))) {
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
                'richtext' => 'nullable|boolean',
                'options' => [
                    'nullable',
                    function ($attribute, $value, $fail) {
                        if (! json_decode($value, true)) {
                            $fail('Must be valid json');
                        }
                    },
                ],
            ],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getConfigurationFields(): array
    {
        return [
            [
                'key' => 'richtext',
                'type' => 'toggle',
                'label' => __('lunar::fieldtypes.richtext'),
                'hint' => __('lunar::fieldtypes.richtext_hint'),
            ],
        ];
    }
}
