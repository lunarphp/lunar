<?php

namespace Lunar\Exceptions\FieldTypes;

use Lunar\Exceptions\LunarException;

class FieldTypeMissingException extends LunarException
{
    public function __construct(string $classname)
    {
        $this->message = __('lunar::exceptions.fieldtype_missing', [
            'class' => $classname,
        ]);
    }
}
