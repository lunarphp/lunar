<?php

namespace Lunar\Core\Contracts;

use Lunar\Core\Models\Cart;

interface CouponValidator
{
    /**
     * Validate a coupon for whether the cart's customer can use it. The cart
     * defaults to the current cart session.
     */
    public function validate(string $coupon, ?Cart $cart = null): bool;
}
