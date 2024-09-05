<?php

namespace Lunar\Base;

use Closure;
use Lunar\Models\CartLine;
use Lunar\Models\Contracts\CartLine;

abstract class CartLineModifier
{
    /**
     * Called just before cart totals are calculated.
     */
    public function calculating(CartLine $cartLine, Closure $next): CartLine
    {
        return $next($cartLine);
    }

    /**
     * Called just after cart totals are calculated.
     */
    public function calculated(CartLine $cartLine, Closure $next): CartLine
    {
        return $next($cartLine);
    }

    /**
     * Called just after cart sub total is calculated.
     */
    public function subtotalled(CartLine $cartLine, Closure $next): CartLine
    {
        return $next($cartLine);
    }
}
