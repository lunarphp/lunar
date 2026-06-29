<?php

namespace Lunar\Core\Contracts\Actions\Carts;

use Illuminate\Support\Collection;
use Lunar\Core\Models\CartLine;

interface CalculatesLineSubtotal
{
    public function execute(
        CartLine $cartLine,
        Collection $customerGroups
    ): CartLine;
}
