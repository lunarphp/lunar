<?php

namespace Lunar\Core\Actions\Carts;

use Lunar\Core\Contracts\Actions\Carts\SetsCartLineFulfilmentMethod;
use Lunar\Core\Models\CartLine;

class SetCartLineFulfilmentMethod implements SetsCartLineFulfilmentMethod
{
    public function execute(CartLine $cartLine, ?string $fulfilmentMethod): CartLine
    {
        /** @var CartLine $cartLine */
        $cartLine->update(['fulfilment_method' => $fulfilmentMethod]);

        return $cartLine;
    }
}
