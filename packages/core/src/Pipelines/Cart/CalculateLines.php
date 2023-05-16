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

            $purchasable = $cartLine->purchasable;
            $unitQuantity = $purchasable->getUnitQuantity();

            $unitPrice = ($cartLine->unitPrice->decimal / $unitQuantity) * $cart->currency->factor;

            $subTotal = (int) round($unitPrice * $cartLine->quantity, $cart->currency->decimal_places);

            $cartLine->subTotal = new Price($subTotal, $cart->currency, $unitQuantity);
            $cartLine->taxAmount = new Price(0, $cart->currency, $unitQuantity);
            $cartLine->total = new Price($subTotal, $cart->currency, $unitQuantity);
            $cartLine->unitPrice = new Price((int) round($unitPrice), $cart->currency, $unitQuantity);
            $cartLine->discountTotal = new Price(0, $cart->currency, $unitQuantity);
        }

        return $next($cart);
    }
}
