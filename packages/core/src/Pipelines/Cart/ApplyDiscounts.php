<?php

namespace Lunar\Pipelines\Cart;

use Closure;
use Lunar\Models\Cart;
use Lunar\Facades\Discounts;

final class ApplyDiscounts
{
    /**
     * Called just before cart totals are calculated.
     *
     * @return void
     */
    public function handle(Cart $cart, Closure $next)
    {
        Discounts::apply($cart);

        return $next($cart);
    }
}
