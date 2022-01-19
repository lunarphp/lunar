<?php

namespace GetCandy\Exceptions\FieldTypes;

use Exception;

class FieldTypeMissingException extends Exception
{
    public function __construct($classname)
    {
        $this->message = __('getcandy::exceptions.fieldtype_missing', [
            'class' => $classname,
        ]);
    }
}
