<?php

namespace Lunar\Core\Actions\Orders;

use Lunar\Core\Actions\AbstractAction;
use Lunar\Core\Models\Contracts\Order as OrderContract;
use Lunar\Core\Models\Order;

/**
 * Convenience wrapper that transitions an order into the configured
 * "shipped" status (defaults to `dispatched`).
 *
 * Today this is `UpdateOrderStatus` with a fixed status string; once a
 * `shipped_at` column or a state-machine subsystem lands, the body changes
 * without altering this public signature.
 */
final class MarkOrderAsShipped extends AbstractAction
{
    public function execute(OrderContract $order): Order
    {
        $status = (string) config('lunar.orders.shipped_status', 'dispatched');

        return UpdateOrderStatus::run($order, $status);
    }
}
