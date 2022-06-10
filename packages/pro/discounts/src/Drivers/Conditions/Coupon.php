<?php

namespace GetCandy\Discounts\Drivers\Conditions;

use GetCandy\Discounts\Models\DiscountCondition;
use GetCandy\Models\Cart;

class Coupon
{
    protected DiscountCondition $condition;

    public function with(DiscountCondition $discountCondition)
    {
        $this->condition = $discountCondition;

        return $this;
    }

    public function check(Cart $cart): bool
    {
        $cartCoupon = $cart->meta->coupon ?? null;
        $conditionCoupon = $this->condition->data->coupon ?? null;

        return $cartCoupon && ($cartCoupon === $conditionCoupon);
    }
}
