<?php

namespace Lunar\Base;

use Closure;
use Lunar\Models\CartLine;

abstract class CartLineModifier
{
    /**
     * Called just before cart totals are calculated.
     *
     * @return CartLine
     */
    public function calculating(CartLine $cartLine, Closure $next): CartLine
    {
        return $next($cartLine);
    }

    /**
     * Called just after cart totals are calculated.
     *
     * @return CartLine
     */
    public function calculated(CartLine $cartLine, Closure $next): CartLine
    {
        return $next($cartLine);
    }
}
