<?php

namespace Lunar\Base;

use Lunar\Models\Contracts\Cart;

interface DiscountTypeInterface
{
    /**
     * Return the name of the discount type.
     */
    public function getName(): string;

    /**
     * Execute and apply the discount if conditions are met.
     */
    public function apply(Cart $cart): Cart;
}
