<?php

namespace Lunar\Core\Contracts\Actions\Orders;

use Lunar\Core\Models\Contracts\Order as OrderContract;
use Lunar\Core\Models\Order;

interface ClosesOrder
{
    /**
     * Close (archive) an order — it has been fully dealt with and drops out of
     * the open work queue.
     */
    public function execute(OrderContract $order): Order;
}
