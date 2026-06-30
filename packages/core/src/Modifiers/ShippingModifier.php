<?php

namespace Lunar\Core\Modifiers;

use Closure;
use Lunar\Core\Models\Cart;

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
