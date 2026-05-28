<?php

namespace Lunar\Core\Actions\Orders;

use Lunar\Core\Contracts\Actions\Orders\MarksOrderAsShipped;
use Lunar\Core\Models\Contracts\Order as OrderContract;
use Lunar\Core\Models\Order;
use Lunar\Core\States\Order\Fulfilment\Shipped;

/**
 * Convenience wrapper that transitions an order's fulfilment_status into
 * Shipped. The OrderObserver recomputes order_status from the new
 * fulfilment_status + current payment_status.
 */
final class MarkOrderAsShipped implements MarksOrderAsShipped
{
    public function execute(OrderContract $order): Order
    {
        /** @var Order $order */
        $order->fulfilment_status->transitionTo(Shipped::class);

        return $order->refresh();
    }
}
