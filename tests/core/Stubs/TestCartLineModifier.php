<?php

namespace Lunar\Tests\Core\Stubs;

use Closure;
use Lunar\Core\DataObjects\PriceValue;
use Lunar\Core\Models\CartLine;
use Lunar\Core\Modifiers\CartLineModifier;

class TestCartLineModifier extends CartLineModifier
{
    public function calculating(CartLine $cartLine, Closure $next): CartLine
    {
        $cartLine->unitPrice = new PriceValue(1000, $cartLine->cart->currency);

        return $next($cartLine);
    }
}
