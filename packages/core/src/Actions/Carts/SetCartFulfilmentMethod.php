<?php

namespace Lunar\Core\Actions\Carts;

use Lunar\Core\Contracts\Actions\Carts\SetsCartFulfilmentMethod;
use Lunar\Core\Models\Cart;

class SetCartFulfilmentMethod implements SetsCartFulfilmentMethod
{
    public function execute(Cart $cart, ?string $fulfilmentMethod): void
    {
        /** @var Cart $cart */
        $cart->update(['fulfilment_method' => $fulfilmentMethod]);
    }
}
