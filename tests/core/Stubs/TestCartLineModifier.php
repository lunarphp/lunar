<?php

namespace Stubs;

use Closure;
use Lunar\Base\CartLineModifier;
use Lunar\DataTypes\Price;
use Lunar\Models\CartLine;

class TestCartLineModifier extends CartLineModifier
{
    public function calculating(CartLine $cartLine, Closure $next): CartLine
    {
        $cartLine->unitPrice = new Price(1000, $cartLine->cart->currency, 1);

        return $next($cartLine);
    }
}
