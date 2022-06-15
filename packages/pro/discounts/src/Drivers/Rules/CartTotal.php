<?php

namespace GetCandy\Discounts\Drivers\Rules;

use GetCandy\Discounts\Http\Livewire\Components\CouponEdit;
use GetCandy\Discounts\Interfaces\DiscountRuleInterface;
use GetCandy\Discounts\Models\DiscountRule;
use GetCandy\Models\Cart;

class CartTotal implements DiscountRuleInterface
{
    protected DiscountRule $rule;

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return 'Cart Total';
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
        $priceCheck = $this->rule->getData('totals.'.$cart->currency->code, null);

        return $priceCheck && ($cart->subTotal->value >= $priceCheck);
    }

    public function editComponent(): string
    {
        return (new CouponEdit)->getName();
    }
}
