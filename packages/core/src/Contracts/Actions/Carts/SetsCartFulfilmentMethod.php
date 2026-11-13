<?php

namespace Lunar\Core\Contracts\Actions\Carts;

use Lunar\Core\Models\Cart;

interface SetsCartFulfilmentMethod
{
    public function execute(Cart $cart, ?string $fulfilmentMethod): void;
}
