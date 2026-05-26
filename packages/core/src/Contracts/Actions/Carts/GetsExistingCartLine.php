<?php

namespace Lunar\Core\Contracts\Actions\Carts;

use Lunar\Core\Contracts\Purchasable;
use Lunar\Core\Models\Contracts\Cart as CartContract;
use Lunar\Core\Models\Contracts\CartLine as CartLineContract;

interface GetsExistingCartLine
{
    public function execute(
        CartContract $cart,
        Purchasable $purchasable,
        array $meta = []
    ): ?CartLineContract;
}
