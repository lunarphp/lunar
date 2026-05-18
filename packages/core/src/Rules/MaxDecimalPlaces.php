<?php

namespace Lunar\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class MaxDecimalPlaces implements ValidationRule
{
    protected $maxDecimals = 2;

    public function __construct($maxDecimals = 2)
    {
        $this->maxDecimals = $maxDecimals;
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ((int) strpos(ltrim(strrev($value), '0'), '.') > $this->maxDecimals) {
            $fail('The :attribute must be no more than '.$this->maxDecimals.' decimal place(s).');
        }
    }
}
