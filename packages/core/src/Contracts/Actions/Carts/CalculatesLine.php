<?php

namespace Lunar\Core\Contracts\Actions\Carts;

use Illuminate\Support\Collection;
use Lunar\Core\Contracts\Addressable;
use Lunar\Core\Models\CartLine;

interface CalculatesLine
{
    public function execute(
        CartLine $cartLine,
        Collection $customerGroups,
        ?Addressable $shippingAddress = null,
        ?Addressable $billingAddress = null
    ): CartLine;
}
