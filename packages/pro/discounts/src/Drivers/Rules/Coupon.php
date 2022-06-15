<?php

namespace GetCandy\Discounts\Drivers\Rules;

use GetCandy\Discounts\Http\Livewire\Components\CouponEdit;
use GetCandy\Discounts\Interfaces\DiscountRuleInterface;
use GetCandy\Discounts\Models\DiscountRule;
use GetCandy\Models\Cart;

class Coupon implements DiscountRuleInterface
{
    protected DiscountRule $rule;

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
    public function with(DiscountRule $discountRule): self
    {
        $this->rule = $discountRule;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function check(Cart $cart): bool
    {
        $cartCoupon = strtoupper($cart->meta->coupon ?? null);
        $conditionCoupon = strtoupper($this->rule->data->coupon ?? null);

        return $cartCoupon && ($cartCoupon === $conditionCoupon);
    }

    public function editComponent(): string
    {
        return (new CouponEdit)->getName();
    }
}
