<?php

use Lunar\Base\Validation\CouponValidator;

return [

    /*
    |--------------------------------------------------------------------------
    | Coupon Validator
    |--------------------------------------------------------------------------
    |
    | Here you can specify the class which validates coupons. This is useful if you
    | want to have custom logic for what determines whether a coupon can be used.
    |
    */
    'coupon_validator' => CouponValidator::class,

];
