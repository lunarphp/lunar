<?php

namespace Lunar\Core\Contracts\Actions\Carts;

use Lunar\Core\Contracts\Purchasable;
use Lunar\Core\Models\Cart;
use Lunar\Core\Models\CartLine;

interface GetsExistingCartLine
{
    public function execute(
        Cart $cart,
        Purchasable $purchasable,
        array $meta = []
    ): ?CartLine;
}
