<?php

namespace Lunar\Core\Base;

use Closure;
use Lunar\Core\Models\Contracts\Cart;

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
