<?php

namespace Lunar\Pipelines\CartLine;

use Closure;
use Lunar\DataTypes\Price;
use Lunar\Models\CartLine;

class CalculateSubTotal
{
    /**
     * Called just before cart totals are calculated.
     *
     * @return void
     */
    public function handle(CartLine $cartLine, Closure $next)
    {
        $cart = $cartLine->cart;

        $unitPrice = $cartLine->unitPrice->unitDecimal(false) * $cart->currency->factor;

        $subTotal = (int) round($unitPrice * $cartLine->quantity, $cart->currency->decimal_places);

        $cartLine->subTotal = new Price($subTotal, $cart->currency, 1);
        $cartLine->taxAmount = new Price(0, $cart->currency, 1);
        $cartLine->total = new Price($subTotal, $cart->currency, 1);
        $cartLine->subTotalDiscounted = new Price($subTotal, $cart->currency, 1);
        $cartLine->discountTotal = new Price(0, $cart->currency, 1);

        return $next($cartLine);
    }
}
