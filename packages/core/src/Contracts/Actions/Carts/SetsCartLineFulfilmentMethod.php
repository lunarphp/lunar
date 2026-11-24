<?php

namespace Lunar\Core\Contracts\Actions\Carts;

use Lunar\Core\Models\CartLine;

interface SetsCartLineFulfilmentMethod
{
    public function execute(CartLine $cartLine, ?string $fulfilmentMethod): CartLine;
}
