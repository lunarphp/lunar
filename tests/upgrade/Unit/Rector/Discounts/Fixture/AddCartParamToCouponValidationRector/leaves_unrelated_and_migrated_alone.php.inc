<?php

namespace Lunar\Tests\Upgrade\Unit\Rector\Discounts\Fixture\AddCartParamToCouponValidationRector;

use Lunar\Core\Contracts\CouponValidator;
use Lunar\Core\Models\Cart;

class FormValidator
{
    // Not a coupon validator.
    public function validate(string $input): bool
    {
        return $input !== '';
    }
}

class MigratedCouponValidator implements CouponValidator
{
    public function validate(string $coupon, ?Cart $cart = null): bool
    {
        return true;
    }
}
