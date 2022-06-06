<?php

namespace GetCandy\Base;

use GetCandy\Models\Cart;

abstract class CartModifier
{
    /**
     * Handle the process calculating pipeline
     *
     * @param CartLine $cartLine
     * @param \Closure $next
     *
     * @return \Closure
     */
    public function processCalculating(Cart $cart, $next)
    {
        $this->calculating($cart);

        return $next($cart);
    }

    /**
     * Handle the process calculating pipeline
     *
     * @param CartLine $cartLine
     * @param \Closure $next
     *
     * @return \Closure
     */
    public function processCalculated(Cart $cart, $next)
    {
        $this->calculated($cart);

        return $next($cart);
    }

    /**
     * Called just before cart totals are calculated.
     *
     * @return void
     */
    public function calculating(Cart $cart)
    {
        //
    }

    /**
     * Called just after cart totals are calculated.
     *
     * @return void
     */
    public function calculated(Cart $cart)
    {
        //
    }
}
