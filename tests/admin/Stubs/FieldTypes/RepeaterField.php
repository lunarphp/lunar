<?php

namespace Lunar\Tests\Admin\Stubs\FieldTypes;

use JsonSerializable;
use Lunar\Base\FieldType;
use Lunar\Exceptions\FieldTypeException;

class RepeaterField implements FieldType, JsonSerializable
{
    protected $value;

    public function __construct($value = [])
    {
        $this->setValue($value);
    }

    public function jsonSerialize(): mixed
    {
        return $this->value;
    }

    public function getValue()
    {
        return json_decode($this->value ?? '[]', true);
    }

    public function setValue($value)
    {
        if ($value === null) {
            $value = [];
        }
        if (! is_array($value)) {
            throw new FieldTypeException(self::class.' value must be an array.');
        }

        $this->value = json_encode($value);
    }

    public function getConfig(): array
    {
        return [
            'options' => [],
        ];
    }
}
