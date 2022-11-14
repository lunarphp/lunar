<?php

namespace Lunar\Pipelines\Cart;

use Closure;
use Illuminate\Pipeline\Pipeline;
use Lunar\DataTypes\Price;
use Lunar\Facades\Taxes;
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

            $unitPrice = (int) round(
                (($cartLine->unitPrice->decimal / $purchasable->getUnitQuantity())
                    * $cart->currency->factor),
                $cart->currency->decimal_places);

            $subTotal = $unitPrice * $cartLine->quantity;

            $taxBreakDown = Taxes::setShippingAddress($cart->shippingAddress)
                ->setBillingAddress($cart->billingAddress)
                ->setCurrency($cart->currency)
                ->setPurchasable($cartLine->purchasable)
                ->setCartLine($cartLine)
                ->getBreakdown($subTotal);

            $taxTotal = $taxBreakDown->amounts->sum('price.value');

            $cartLine->taxBreakdown = $taxBreakDown;
            $cartLine->subTotal = new Price($subTotal, $cart->currency, $unitQuantity);
            $cartLine->taxAmount = new Price($taxTotal, $cart->currency, $unitQuantity);
            $cartLine->total = new Price($subTotal + $taxTotal, $cart->currency, $unitQuantity);
            $cartLine->unitPrice = new Price($unitPrice, $cart->currency, $unitQuantity);
            $cartLine->discountTotal = new Price(0, $cart->currency, $unitQuantity);
        }

        return $next($cart);
    }
}
