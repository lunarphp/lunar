<?php

namespace Lunar\Core\Actions\Orders;

use Lunar\Core\Contracts\Actions\Orders\MarksOrderAsShipped;
use Lunar\Core\Models\Contracts\Order as OrderContract;
use Lunar\Core\Models\Order;
use Lunar\Core\States\Order\Order\Shipped;

/**
 * Convenience wrapper that transitions an order's status into Shipped.
 */
final class MarkOrderAsShipped implements MarksOrderAsShipped
{
    public function execute(OrderContract $order): Order
    {
        /** @var Order $order */
        $order->status->transitionTo(Shipped::class);

        return $order->refresh();
    }
}
