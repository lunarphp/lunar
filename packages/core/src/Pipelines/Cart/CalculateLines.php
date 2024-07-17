<?php

namespace Lunar\Pipelines\Cart;

use Closure;
use Illuminate\Pipeline\Pipeline;
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
            app(Pipeline::class)
                ->send($line)
                ->through(
                    config('lunar.cart.pipelines.cart_lines', [])
                )->thenReturn(function ($cartLine) {
                    $cartLine->cacheProperties();

                    return $cartLine;
                });
        }

        return $next($cart);
    }
}
