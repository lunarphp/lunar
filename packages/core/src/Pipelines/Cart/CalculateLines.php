<?php

namespace Lunar\Pipelines\Cart;

use Closure;
use Illuminate\Pipeline\Pipeline;
use Lunar\DataTypes\Price;
use Lunar\Models\Cart;
use Lunar\Models\Contracts\Cart as CartContract;
use Spatie\LaravelBlink\BlinkFacade as Blink;

class CalculateLines
{
    /**
     * Called just before cart totals are calculated.
     *
     * @param  Closure(CartContract): mixed  $next
     */
    public function handle(CartContract $cart, Closure $next): mixed
    {
        /** @var Cart $cart */

        // Publish the cart-level tax zone override into the Blink request-scope
        // store so Price::priceIncTax() / priceExTax() pick it up while
        // GetUnitPrice computes the per-unit display price for each line.
        if ($cart->taxZone) {
            Blink::put('lunar_cart_tax_zone', $cart->taxZone);
        } else {
            Blink::forget('lunar_cart_tax_zone');
        }

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
