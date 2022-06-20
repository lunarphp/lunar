<?php

namespace GetCandy\Discounts\Managers;

use GetCandy\Base\DataTransferObjects\CartDiscount;
use GetCandy\Discounts\Facades\Discounts;
use GetCandy\Discounts\Models\DiscountRuleset;
use GetCandy\Models\Cart;

class DiscountRulesetManager
{
    public function __construct(
        protected DiscountRuleset $discountRuleset
    ) {
        //
    }

    public function check(Cart $cart)
    {
        $passes = true;

        $criteria = $this->discountRuleset->criteria;

        foreach ($this->discountRuleset->rules as $rule) {
            $result = $rule->driver()->check($cart);

            /**
             * If the rule check passes and we allow any then we
             * just return out as no further checks are needed.
             */
            if ($result && $criteria == 'any') {
                Discounts::addDiscount(
                    new CartDiscount(
                        $this->discountRuleset->discount->translateAttribute('name'),
                        $this->discountRuleset->discount->id
                    )
                );

                return true;
            }
            $passes = $result;

            if ($passes) {
                Discounts::addDiscount(
                    new CartDiscount(
                        $this->discountRuleset->discount->translateAttribute('name'),
                        $this->discountRuleset->discount->id
                    )
                );
            }
        }

        return $passes;
    }
}
