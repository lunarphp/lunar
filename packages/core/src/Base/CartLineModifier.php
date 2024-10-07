<?php

namespace Lunar\Base;

use Closure;
use Lunar\Models\Contracts\CartLine as CartLineContract;

abstract class CartLineModifier
{
    /**
     * Called just before cart totals are calculated.
     */
    public function calculating(CartLineContract $cartLine, Closure $next): CartLineContract
    {
        return $next($cartLine);
    }

    /**
     * Called just after cart totals are calculated.
     */
    public function calculated(CartLineContract $cartLine, Closure $next): CartLineContract
    {
        return $next($cartLine);
    }

    /**
     * Called just after cart sub total is calculated.
     */
    public function subtotalled(CartLineContract $cartLine, Closure $next): CartLineContract
    {
        return $next($cartLine);
    }
}
