<?php

namespace GetCandy\Tests\Stubs;

use GetCandy\Base\CartModifier;
use GetCandy\Models\Cart;

class TestCartModifier extends CartModifier
{
    /**
     * Called just before cart totals are calculated.
     *
     * @return void
     */
    public function calculating(Cart $cart)
    {
        // $cart->total->value = 5000;
    }

    /**
     * Called just after cart totals are calculated.
     *
     * @return void
     */
    public function calculated(Cart $cart)
    {
        $cart->total->value = 5000;
    }
}
