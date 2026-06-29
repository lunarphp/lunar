<?php

namespace Lunar\Core\Contracts\Actions\Orders;

use Lunar\Core\Models\Order;

interface CancelsOrder
{
    /**
     * Cancel an order that hasn't been fulfilled, voiding its open fulfilments and
     * closing it. Does not issue a refund or restock — those are separate
     * concerns (future specs).
     */
    public function execute(Order $order, ?string $reason = null, ?string $note = null, bool $notify = true): Order;
}
