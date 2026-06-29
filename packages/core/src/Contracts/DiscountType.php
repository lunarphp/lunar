<?php

namespace Lunar\Core\Contracts;

use Lunar\Core\Models\Cart;

interface DiscountType
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
