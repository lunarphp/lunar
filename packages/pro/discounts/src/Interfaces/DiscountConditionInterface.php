<?php

namespace GetCandy\Discounts\Interfaces;

use GetCandy\Discounts\Models\DiscountCondition;
use GetCandy\Models\Cart;

interface DiscountConditionInterface
{
    /**
     * Return the driver name
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Set the condition to use for the driver.
     *
     * @param DiscountCondition $discountCondition
     *
     * @return self
     */
    public function with(DiscountCondition $discountCondition): self;

    /**
     * Check the criteria for the condition
     *
     * @param Cart $cart
     *
     * @return bool
     */
    public function check(Cart $cart): bool;

    /**
     * Reference to the component used for editing the condition
     *
     * @return string
     */
    public function editComponent(): string;
}
