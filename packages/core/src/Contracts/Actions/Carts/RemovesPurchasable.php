<?php

namespace Lunar\Core\Contracts\Actions\Carts;

use Lunar\Core\Models\Contracts\Cart as CartContract;

interface RemovesPurchasable
{
    public function execute(
        CartContract $cart,
        int $cartLineId
    ): void;
}
