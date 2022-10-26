<?php

namespace Lunar\Pipelines\Cart;

use Closure;
use Lunar\Models\Cart;

class ApplyShipping
{
    /**
     * Called just before cart totals are calculated.
     *
     * @return void
     */
    public function handle(Cart $cart, Closure $next): Cart
    {
        return $next($cart);
    }
}
