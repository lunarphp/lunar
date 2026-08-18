<?php

namespace Lunar\Core\FieldTypes;

use Illuminate\Support\Collection;
use Lunar\Core\Exceptions\FieldTypeException;

class TranslatedText extends AbstractFieldType
{
    public function setValue(mixed $value): void
    {
        if (blank($value)) {
            $this->value = new Collection;

            return;
        }

        if (is_array($value)) {
            $value = collect($value);
        }

        if (! $value instanceof Collection) {
            throw new FieldTypeException(self::class.' value must be a collection.');
        }

        foreach ($value as $key => $item) {
            if (is_string($item) || is_numeric($item) || is_bool($item)) {
                $item = new Text($item);
                $value[$key] = $item;
            }
            if ($item && (get_class($item) !== Text::class)) {
                throw new FieldTypeException(self::class.' only supports '.Text::class.' field types.');
            }
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
