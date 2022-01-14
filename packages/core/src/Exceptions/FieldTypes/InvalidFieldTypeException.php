<?php

namespace GetCandy\Exceptions\FieldTypes;

use Exception;

class InvalidFieldTypeException extends Exception
{
    public function __construct($classname)
    {
        $this->message = __('getcandy::exceptions.invalid_fieldtype', [
            'class' => $classname,
        ]);
    }
}
