<?php

namespace Lunar\Exceptions\FieldTypes;

use Lunar\Exceptions\LunarException;

class InvalidFieldTypeException extends LunarException
{
    public function __construct(string $classname)
    {
        $this->message = __('lunar::exceptions.invalid_fieldtype', [
            'class' => $classname,
        ]);
    }
}
