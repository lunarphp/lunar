<?php

namespace GetCandy\Discounts\Managers;

use GetCandy\Base\DataTransferObjects\CartDiscount;
use GetCandy\Discounts\Models\Discount;
use GetCandy\Discounts\Models\DiscountRuleset;
use GetCandy\Models\Cart;
use Illuminate\Support\Collection;

class DiscountManager
{
    /**
     * The currently applied discounts
     *
     * @var Collection
     */
    public Collection $appliedDiscounts;

    /**
     * Initiate the class
     */
    public function __construct()
    {
        $this->appliedDiscounts = collect();
    }


    public function addDiscount(CartDiscount $cartDiscount)
    {
        $this->appliedDiscounts = $this->appliedDiscounts
            ->push($cartDiscount)->unique('identifier');

        return $this;
    }

    public function getApplied()
    {
        return $this->appliedDiscounts;
    }

    public function ruleset(DiscountRuleset $discountRuleset)
    {
        return new DiscountRulesetManager($discountRuleset);
    }

    public function run(Cart $cart)
    {
        dd($cart);
    }

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
