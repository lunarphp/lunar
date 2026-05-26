<?php

namespace Lunar\Core\Pipelines\Cart;

use Closure;
use Lunar\Core\DataObjects\PriceValue;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\Contracts\Cart as CartContract;

class Calculate
{
    /**
     * Called just before cart totals are calculated.
     *
     * @param  Closure(CartContract): mixed  $next
     */
    public function handle(CartContract $cart, Closure $next): mixed
    {
        /** @var Cart $cart */
        $discountTotal = $cart->lines->sum('discountTotal.value');

        $subTotal = $cart->lines->sum('subTotal.value');

        $total = $cart->lines->sum('total.value') + $cart->shippingTotal?->value;

        $subTotalDiscounted = $cart->lines->sum(function ($line) {
            return $line->subTotalDiscounted ?
                $line->subTotalDiscounted->value :
                $line->subTotal->value;
        });

        $cart->subTotal = new PriceValue($subTotal, $cart->currency);
        $cart->subTotalDiscounted = new PriceValue($subTotalDiscounted, $cart->currency);
        $cart->discountTotal = new PriceValue($discountTotal, $cart->currency);
        $cart->total = new PriceValue($total, $cart->currency);

        return $next($cart);
    }
}
