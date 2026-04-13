<?php

namespace Lunar\FieldTypes;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use JsonSerializable;
use Lunar\Base\FieldType;
use Lunar\Exceptions\FieldTypeException;

class ListField implements Arrayable, FieldType, JsonSerializable
{
    /**
     * @var array
     */
    protected $value;

    /**
     * Create a new instance of List field type.
     *
     * @param  array  $value
     */
    public function __construct($value = [])
    {
        $this->setValue($value);
    }

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
     * Return the value of this field.
     *
     * @return array
     */
    public function getValue()
    {
        return json_decode($this->value ?? '[]');
    }

    /**
     * Set the value of this field.
     *
     * @param  array|string  $value
     */
    public function setValue($value)
    {
        if (! is_array($value)) {
            throw new FieldTypeException(self::class.' value must be an array.');
        }

        $this->value = json_encode($value);
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
     * Return the value as an array (implements Arrayable for Filament 4 compatibility).
     */
    public function toArray(): array
    {
        return Arr::wrap((array) json_decode($this->value ?? '[]', associative: true));
    }
}
