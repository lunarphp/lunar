<?php

namespace GetCandy\Base;

use GetCandy\Models\Cart;
use GetCandy\Models\CartLine;

abstract class CartLineModifier
{
    /**
     * Handle the process calculating pipeline
     *
     * @param CartLine $cartLine
     * @param \Closure $next
     *
     * @return \Closure
     */
    public function processCalculating(CartLine $cartLine, $next)
    {
        $this->calculating($cartLine);

        return $next($cartLine);
    }

    /**
     * Handle the process calculating pipeline
     *
     * @param CartLine $cartLine
     * @param \Closure $next
     *
     * @return \Closure
     */
    public function processCalculated(CartLine $cartLine, $next)
    {
        $this->calculated($cartLine);

        return $next($cartLine);
    }

    /**
     * Called just before cart totals are calculated.
     *
     * @return void
     */
    public function calculating(CartLine $cartLine)
    {
        //
    }

    /**
     * Called just after cart totals are calculated.
     *
     * @return void
     */
    public function calculated(CartLine $cartLine)
    {
        //
    }
}
