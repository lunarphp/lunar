<?php

namespace GetCandy\Base;

use GetCandy\Models\Cart;
use GetCandy\Models\CartLine;

abstract class CartLineModifier
{
    /**
     * Called just before cart totals are calculated.
     *
     * @return void
     */
    public function calculating(CartLine $cartLine)
    {
        //
    }

    /**
     * Called just after cart totals are calculated.
     *
     * @return void
     */
    public function calculated(CartLine $cartLine)
    {
        //
    }
}
