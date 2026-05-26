<?php

namespace Lunar\Core\Contracts;

interface CouponValidator
{
    /**
     * Validate a coupon for whether it can be used.
     */
    public function validate(string $coupon): bool;
}
