<?php

namespace GetCandy\Discounts\Drivers\Conditions;

use GetCandy\Discounts\Models\DiscountCondition;
use GetCandy\Models\Cart;

class Product
{
    protected DiscountCondition $condition;

    public function with(DiscountCondition $discountCondition)
    {
        $this->condition = $discountCondition;

        return $this;
    }

    public function check(Cart $cart): bool
    {
        return $this->condition
            ->purchasables()
            ->whereIn(
                'purchasable_id',
                $cart->lines->pluck('purchasable_id')
            )->exists();
    }
}
