<?php

namespace Lunar\Core\FieldTypes;

use Lunar\Core\Exceptions\FieldTypeException;

class Toggle extends AbstractFieldType
{
    protected mixed $value = false;

    public function __toString(): string
    {
        return (string) (int) ($this->value ?? false);
    }

    public function setValue(mixed $value): void
    {
        if ($value && is_array($value)) {
            throw new FieldTypeException(self::class.' value must be a string or boolean.');
        }

        $this->value = $value ?: false;
    }
}
