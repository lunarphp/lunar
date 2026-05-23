<?php

namespace Lunar\Core\Actions\Orders;

use Lunar\Core\Actions\AbstractAction;
use Lunar\Core\Events\Orders\OrderStatusUpdated;
use Lunar\Core\Exceptions\OrderActionException;
use Lunar\Core\Models\Contracts\Order as OrderContract;
use Lunar\Core\Models\Order;

/**
 * Apply a status change to an order.
 *
 * Deliberately thin in v2 — the canonical entry point for status mutation so
 * callers (Filament actions, the API, the CLI) all go through one seam.
 * When the state-machines TODO item lands, the body is re-implemented as a
 * transition without changing this signature.
 */
final class UpdateOrderStatus extends AbstractAction
{
    public function execute(OrderContract $order, string $status): Order
    {
        /** @var Order $order */
        if (! array_key_exists($status, config('lunar.orders.statuses', []))) {
            throw new OrderActionException(
                "Status [{$status}] is not configured under lunar.orders.statuses."
            );
        }

        $previous = $order->status;

        if ($previous === $status) {
            return $order;
        }

        $order->forceFill(['status' => $status])->save();

        OrderStatusUpdated::dispatch($order, $previous, $status);

        return $order;
    }
}
