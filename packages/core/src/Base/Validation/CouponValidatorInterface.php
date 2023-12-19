<?php

namespace Lunar\Base\Validation;

interface CouponValidatorInterface
{
    /**
     * Validate a coupon for whether it can be used.
     */
    public function validate(string $coupon): bool;
}
