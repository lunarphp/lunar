<?php

namespace Lunar\Core\Exceptions\FieldTypes;

use Lunar\Core\Exceptions\LunarException;

class InvalidFieldTypeException extends LunarException
{
    public function __construct(string $classname)
    {
        $this->message = __('lunar::exceptions.invalid_fieldtype', [
            'class' => $classname,
        ]);
    }
}
