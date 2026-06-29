<?php

namespace Lunar\Core\Contracts\Actions\Orders;

use Lunar\Core\Models\Order;

interface ReopensOrder
{
    /**
     * Reopen (un-archive) a closed order, returning it to the open work queue.
     */
    public function execute(Order $order): Order;
}
