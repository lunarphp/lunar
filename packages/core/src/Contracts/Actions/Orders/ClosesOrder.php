<?php

namespace Lunar\Core\Contracts\Actions\Orders;

use Lunar\Core\Models\Order;

interface ClosesOrder
{
    /**
     * Close (archive) an order — it has been fully dealt with and drops out of
     * the open work queue.
     */
    public function execute(Order $order): Order;
}
