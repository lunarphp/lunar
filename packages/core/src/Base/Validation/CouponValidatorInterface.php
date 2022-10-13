<?php

namespace Lunar\Base\Validation;

interface CouponValidatorInterface
{
    /**
     * Validate a coupon for whether it can be used.
     *
     * @param string $coupon
     *
     * @return bool
     */
    public function validate(string $coupon): bool;
}
