<?php

namespace Lunar\Pipelines\Cart;

use Closure;
use Lunar\DataTypes\Price;
use Lunar\Models\Cart;

class Calculate
{
    /**
     * Called just before cart totals are calculated.
     *
     * @return void
     */
    public function handle(Cart $cart, Closure $next)
    {
        $cart->subTotal = new Price(123, $cart->currency, 1);
        return $next($cart);
    }
}
