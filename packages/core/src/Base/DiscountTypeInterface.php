<?php

namespace Lunar\Base;

use Lunar\Models\Cart;
use Lunar\Models\CartLine;

interface DiscountTypeInterface
{
    /**
     * Return the name of the discount type.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Execute and apply the discount if conditions are met.
     *
     * @param  CartLine  $cartLine
     * @return CartLine
     */
    public function apply(Cart $cart): Cart;
}
