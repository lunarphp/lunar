<?php

namespace Lunar\Core\Contracts\Actions\Carts;

use Lunar\Core\Models\Contracts\Cart as CartContract;
use Lunar\Core\Models\Contracts\Order as OrderContract;

interface CreatesOrder
{
    public function execute(
        CartContract $cart,
        bool $allowMultipleOrders = false,
        ?int $orderIdToUpdate = null
    ): OrderContract;
}
