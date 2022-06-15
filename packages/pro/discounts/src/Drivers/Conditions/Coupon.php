<?php

namespace GetCandy\Discounts\Drivers\Conditions;

use GetCandy\Discounts\Http\Livewire\Components\CouponEdit;
use GetCandy\Discounts\Interfaces\DiscountConditionInterface;
use GetCandy\Discounts\Models\DiscountCondition;
use GetCandy\Models\Cart;

class Coupon implements DiscountConditionInterface
{
    protected DiscountCondition $condition;

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return 'Coupon Code';
    }

    /**
     * {@inheritDoc}
     *
     * @return self
     */
    public function with(DiscountCondition $discountCondition): self
    {
        $this->condition = $discountCondition;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function check(Cart $cart): bool
    {
        $cartCoupon = $cart->meta->coupon ?? null;
        $conditionCoupon = $this->condition->data->coupon ?? null;

        return $cartCoupon && ($cartCoupon === $conditionCoupon);
    }

    public function editComponent(): string
    {
	    return (new CouponEdit)->getName();
    }
}
