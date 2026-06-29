<?php

namespace Lunar\Core\Contracts\Actions\Carts;

use Lunar\Core\Models\Cart;
use Lunar\Core\Models\Order;

interface CreatesOrder
{
    public function execute(
        Cart $cart,
        bool $allowMultipleOrders = false,
        ?int $orderIdToUpdate = null
    ): Order;
}
