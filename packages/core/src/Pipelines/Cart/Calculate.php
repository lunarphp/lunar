<?php

namespace Lunar\Core\Pipelines\Cart;

use Closure;
use Lunar\Core\DataObjects\PriceValue;
use Lunar\Core\Models\Cart;

class Calculate
{
    /**
     * Called just before cart totals are calculated.
     *
     * @param  Closure(Cart):mixed  $next
     */
    public function handle(Cart $cart, Closure $next): mixed
    {
        /** @var Cart $cart */
        $currency = $cart->currency;

        $cart->subTotal = PriceValue::sum($cart->lines->pluck('subTotal'), $currency);
        $cart->subTotalDiscounted = PriceValue::sum(
            $cart->lines->map(fn ($line) => $line->subTotalDiscounted ?: $line->subTotal),
            $currency,
        );
        $cart->discountTotal = PriceValue::sum($cart->lines->pluck('discountTotal'), $currency);

        $linesTotal = PriceValue::sum($cart->lines->pluck('total'), $currency);
        $cart->total = $cart->shippingTotal
            ? $linesTotal->add($cart->shippingTotal)
            : $linesTotal;

        return $next($cart);
    }
}
