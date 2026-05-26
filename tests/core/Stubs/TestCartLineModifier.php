<?php

namespace Lunar\Tests\Core\Stubs;

use Closure;
use Lunar\Core\DataObjects\PriceValue;
use Lunar\Core\Models\Contracts\CartLine as CartLineContract;
use Lunar\Core\Modifiers\CartLineModifier;

class TestCartLineModifier extends CartLineModifier
{
    public function calculating(CartLineContract $cartLine, Closure $next): CartLineContract
    {
        $cartLine->unitPrice = new PriceValue(1000, $cartLine->cart->currency);

        return $next($cartLine);
    }
}
