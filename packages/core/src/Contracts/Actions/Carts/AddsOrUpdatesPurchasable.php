<?php

namespace Lunar\Core\Contracts\Actions\Carts;

use Lunar\Core\Contracts\Purchasable;
use Lunar\Core\Models\Contracts\Cart as CartContract;

interface AddsOrUpdatesPurchasable
{
    public function execute(
        CartContract $cart,
        Purchasable $purchasable,
        int $quantity = 1,
        array $meta = []
    ): void;
}
