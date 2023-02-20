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
        $discountTotal = $cart->lines->sum('discountTotal.value');

        $subTotal = $cart->lines->sum('subTotal.value') - $discountTotal;
        $total = $cart->lines->sum('total.value');

        // Get the shipping address
        if ($shippingAddress = $cart->shippingAddress) {
            if ($shippingAddress->shippingSubTotal) {
                $subTotal += $shippingAddress->shippingSubTotal?->value;
                $total += $shippingAddress->shippingTotal?->value;
            }
        }

        $cart->subTotal = new Price($subTotal, $cart->currency, 1);
        $cart->discountTotal = new Price($discountTotal, $cart->currency, 1);
        $cart->total = new Price($total, $cart->currency, 1);

        return $next($cart);
    }
}
