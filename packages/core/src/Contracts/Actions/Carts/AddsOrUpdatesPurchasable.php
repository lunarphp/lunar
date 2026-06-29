<?php

namespace Lunar\Core\Contracts\Actions\Carts;

use Lunar\Core\Contracts\Purchasable;
use Lunar\Core\Models\Cart;

interface AddsOrUpdatesPurchasable
{
    public function execute(
        Cart $cart,
        Purchasable $purchasable,
        int $quantity = 1,
        array $meta = []
    ): void;
}
