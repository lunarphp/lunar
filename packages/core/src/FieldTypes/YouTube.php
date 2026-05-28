<?php

namespace Lunar\Core\FieldTypes;

use Lunar\Core\Exceptions\FieldTypeException;

class YouTube extends AbstractFieldType
{
    public function setValue(mixed $value): void
    {
        if ($value && (! is_string($value) && ! is_numeric($value) && ! is_bool($value))) {
            throw new FieldTypeException(self::class.' value must be a string.');
        }

        $this->value = $value;
    }
}
