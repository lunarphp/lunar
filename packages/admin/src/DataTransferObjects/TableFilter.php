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

    /**
     * Format the value for display.
     *
     * @param string $value
     * @return void
     */
    public function format($value)
    {
        if ($this->formatter) {
            return call_user_func($this->formatter, $value);
        }

        return $value;
    }
}
