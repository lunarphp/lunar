<?php

namespace Lunar\Core\Pipelines\Cart;

use Closure;
use Lunar\Core\Facades\Discounts;
use Lunar\Core\Models\Cart;

class ApplyDiscounts
{
    /**
     * Called just before cart totals are calculated.
     *
     * @param  Closure(Cart):mixed  $next
     */
    public function handle(Cart $cart, Closure $next): mixed
    {
        /** @var Cart $cart */
        $cart->discounts = collect([]);
        $cart->discountBreakdown = collect([]);

        Discounts::apply($cart);

        return $next($cart);
    }
}
