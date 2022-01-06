<?php

namespace GetCandy\Base;

use GetCandy\Models\Cart;

abstract class CartModifier
{
    /**
     * Called just before cart totals are calculated.
     *
     * @return void
     */
    public function calculating(Cart $cart)
    {
        //
    }

    /**
     * Called just after cart totals are calculated.
     *
     * @return void
     */
    public function calculated(Cart $cart)
    {
        //
    }
}
