<?php

namespace Lunar\Exceptions\FieldTypes;

use Exception;

class InvalidFieldTypeException extends Exception
{
    public function __construct($classname)
    {
        $this->message = __('lunar::exceptions.invalid_fieldtype', [
            'class' => $classname,
        ]);
    }
}
