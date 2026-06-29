<?php

namespace Lunar\Core\Contracts\Actions\Carts;

use Lunar\Core\Models\Cart;

interface RemovesPurchasable
{
    public function execute(
        Cart $cart,
        int $cartLineId
    ): void;
}
