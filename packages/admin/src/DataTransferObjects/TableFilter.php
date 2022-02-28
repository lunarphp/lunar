<?php

namespace GetCandy\Hub\DataTransferObjects;

use Closure;

class TableFilter
{
    public function __construct(
        public string $heading,
        public string $field,
        public ?Closure $formatter = null
    ) {
        // ..
    }

    public function format($value)
    {
        if ($this->formatter) {
            return call_user_func($this->formatter, $value);
        }

        return $value;
    }
}
