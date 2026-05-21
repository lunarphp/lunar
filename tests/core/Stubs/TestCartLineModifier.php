<?php

namespace Lunar\Tests\Core\Stubs;

use Closure;
use Lunar\Core\Base\CartLineModifier;
use Lunar\Core\DataTypes\Price;
use Lunar\Core\Models\Contracts\CartLine as CartLineContract;

class TestCartLineModifier extends CartLineModifier
{
    public function calculating(CartLineContract $cartLine, Closure $next): CartLineContract
    {
        $cartLine->unitPrice = new Price(1000, $cartLine->cart->currency, 1);

        return $next($cartLine);
    }
}
