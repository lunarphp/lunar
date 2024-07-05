<?php

namespace Lunar\Base;

use Closure;
use Lunar\Models\Cart;

abstract class ShippingModifier
{
    /**
     * Called just before cart totals are calculated.
     *
     * @return void
     */
    public function handle(Cart $cart, Closure $next)
    {
        //
    }
}
