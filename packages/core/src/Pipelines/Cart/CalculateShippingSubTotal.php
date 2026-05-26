<?php

namespace Lunar\Core\Pipelines\Cart;

use Closure;
use Lunar\Core\DataObjects\PriceValue;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\Contracts\Cart as CartContract;

final class CalculateShippingSubTotal
{
    /**
     * Sum the shipping breakdown into the cart's shipping sub total.
     *
     * Runs after ApplyShipping so that any pipeline class inserted between
     * the two may mutate $cart->shippingBreakdown and have the sub total
     * recomputed for free.
     *
     * @param  Closure(CartContract): mixed  $next
     */
    public function handle(CartContract $cart, Closure $next): mixed
    {
        /** @var Cart $cart */
        $cart->shippingSubTotal = new PriceValue(
            $cart->shippingBreakdown?->items->sum('price.value') ?? 0,
            $cart->currency,
        );

        return $next($cart);
    }
}
