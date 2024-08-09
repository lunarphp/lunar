<?php

namespace Lunar\FieldTypes;

use Illuminate\Support\Collection;
use JsonSerializable;
use Lunar\Base\FieldType;
use Lunar\Exceptions\FieldTypeException;

class TranslatedText implements FieldType, JsonSerializable
{
    /**
     * @var Collection
     */
    protected $value;

    /**
     * Create a new instance of TranslatedText field type.
     *
     * @param  Collection  $value
     */
    public function __construct($value = null)
    {
        if ($value) {
            $this->setValue($value);
        } else {
            $this->value = new Collection;
        }
    }

    /**
     * Serialize the class.
     *
     * @return Collection
     */
    public function jsonSerialize(): mixed
    {
        return $this->value;
    }

    /**
     * Return the value of this field.
     *
     * @return Collection
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set the value of this field.
     *
     * @param  Collection  $value
     */
    public function setValue($value)
    {
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
                'richtext' => 'nullable',
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
}
