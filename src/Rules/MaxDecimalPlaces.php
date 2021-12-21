<?php

namespace GetCandy\Rules;

use Illuminate\Contracts\Validation\Rule;

class MaxDecimalPlaces implements Rule
{
    protected $maxDecimals = 2;

    public function __construct($maxDecimals = 2)
    {
        $this->maxDecimals = $maxDecimals;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return (int) strpos(ltrim(strrev($value), '0'), '.') <= $this->maxDecimals;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute must be no more than '.$this->maxDecimals.' decimal place(s).';
    }
}
