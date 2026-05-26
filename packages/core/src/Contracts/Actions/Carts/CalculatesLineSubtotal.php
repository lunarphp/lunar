<?php

namespace Lunar\Core\Contracts\Actions\Carts;

use Illuminate\Support\Collection;
use Lunar\Core\Models\Contracts\CartLine as CartLineContract;

interface CalculatesLineSubtotal
{
    public function execute(
        CartLineContract $cartLine,
        Collection $customerGroups
    ): CartLineContract;
}
