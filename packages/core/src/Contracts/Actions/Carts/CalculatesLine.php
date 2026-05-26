<?php

namespace Lunar\Core\Contracts\Actions\Carts;

use Illuminate\Support\Collection;
use Lunar\Core\Contracts\Addressable;
use Lunar\Core\Models\Contracts\CartLine as CartLineContract;

interface CalculatesLine
{
    public function execute(
        CartLineContract $cartLine,
        Collection $customerGroups,
        ?Addressable $shippingAddress = null,
        ?Addressable $billingAddress = null
    ): CartLineContract;
}
