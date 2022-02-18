<?php

namespace GetCandy\Tests\Stubs;

use GetCandy\Base\CartLineModifier;
use GetCandy\DataTypes\Price;
use GetCandy\Models\CartLine;

class TestCartLineModifier extends CartLineModifier
{
    public function calculating(CartLine $cartLine)
    {
        $cartLine->unitPrice = new Price(1000, $cartLine->cart->currency, 1);
    }

    public function calculated(CartLine $cartLine)
    {
    }
}
