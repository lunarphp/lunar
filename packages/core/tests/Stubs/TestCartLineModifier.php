<?php

namespace GetCandy\Tests\Stubs;

use Closure;
use GetCandy\Base\CartLineModifier;
use GetCandy\DataTypes\Price;
use GetCandy\Models\CartLine;

class TestCartLineModifier extends CartLineModifier
{
    public function calculating(CartLine $cartLine, Closure $next): CartLine
    {
        $cartLine->unitPrice = new Price(1000, $cartLine->cart->currency, 1);
        return $next($cartLine);
    }
}
