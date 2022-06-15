<?php

namespace GetCandy\Discounts\Interfaces;

use GetCandy\Discounts\Models\DiscountRule;
use GetCandy\Models\Cart;

interface DiscountRuleInterface
{
    /**
     * Return the driver name.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Set the rule to use for the driver.
     *
     * @param  DiscountRule  $discountRule
     * @return self
     */
    public function with(DiscountRule $discountRule): self;

    /**
     * Check the criteria for the condition.
     *
     * @param  Cart  $cart
     * @return bool
     */
    public function check(Cart $cart): bool;

    /**
     * Reference to the component used for editing the condition.
     *
     * @return string
     */
    public function editComponent(): string;
}
