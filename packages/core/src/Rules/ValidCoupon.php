<?php

namespace Lunar\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Lunar\Base\Validation\CouponValidator;

class ValidCoupon implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $isValid = app(
            config('lunar.discounts.coupon_validator', CouponValidator::class)
        )->validate($value);

        if (! $isValid) {
            $fail('The :attribute is not valid or has been used too many times');
        }
    }
}
