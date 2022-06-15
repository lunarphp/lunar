<?php

namespace GetCandy\Discounts\Managers;

use GetCandy\Discounts\Models\Discount;
use GetCandy\Models\Cart;

class DiscountManager
{
    public function getFreebies(Cart $cart)
    {
        $discounts = Discount::whereHas('conditions', function ($query) {
            $query->whereHas('purchasables');
        })->get();

        $purchasables = collect();

        foreach ($discounts as $discount) {
            foreach ($discount->conditions as $condition) {
                if ($condition->driver()->check($cart)) {
                    foreach ($discount->rewards as $reward) {
                        $purchasables = $purchasables->merge(
                            $reward->purchasables->pluck('purchasable')
                        );
                    }
                }
                continue;
            }
        }

        return $purchasables;
    }
}
