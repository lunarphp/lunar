<?php

namespace Lunar\Pipelines\Cart;

use Closure;
use Illuminate\Pipeline\Pipeline;
use Lunar\DataTypes\Price;
use Lunar\Models\Cart;

class CalculateLines
{
    /**
     * Called just before cart totals are calculated.
     *
     * @return void
     */
    public function handle(Cart $cart, Closure $next)
    {
        foreach ($cart->lines as $line) {
            $cartLine = app(Pipeline::class)
                ->send($line)
                ->through(
                    config('lunar.cart.pipelines.cart_lines', [])
                )->thenReturn(function ($cartLine) {
                    $cartLine->cacheProperties();

                    return $cartLine;
                });

            $unitPrice = $cartLine->unitPrice->unitDecimal(false) * $cart->currency->factor;

            $subTotal = (int) round($unitPrice * $cartLine->quantity, $cart->currency->decimal_places);

            $cartLine->subTotal = new Price($subTotal, $cart->currency, 1);
            $cartLine->taxAmount = new Price(0, $cart->currency, 1);
            $cartLine->total = new Price($subTotal, $cart->currency, 1);
            $cartLine->subTotalDiscounted = new Price($subTotal, $cart->currency, 1);
            $cartLine->discountTotal = new Price(0, $cart->currency, 1);
        }

        return $next($cart);
    }
}
