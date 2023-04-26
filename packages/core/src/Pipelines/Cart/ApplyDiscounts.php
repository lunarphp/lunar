<?php

namespace Lunar\Pipelines\Cart;

use Closure;
use Lunar\Facades\Discounts;
use Lunar\Models\Cart;

final class ApplyDiscounts
{
    /**
     * Called just before cart totals are calculated.
     *
     * @return void
     */
    public function handle(Cart $cart, Closure $next)
    {
        $cart->discountBreakdown = collect([]);

        Discounts::apply($cart);

        return $next($cart);
    }
}
