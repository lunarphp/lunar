<?php

namespace Lunar\Core\Actions\Orders;

use Lunar\Core\Contracts\Actions\Orders\ClosesOrder;
use Lunar\Core\Events\Orders\OrderClosed;
use Lunar\Core\Models\Order;

/**
 * Close (archive) an order. Orders are open by default; closing stamps
 * `closed_at` so the order drops out of the open work queue once it has been
 * fully dealt with (inbox-zero). Idempotent — closing a closed order is a
 * no-op that preserves the original `closed_at`.
 */
class CloseOrder implements ClosesOrder
{
    public function execute(Order $order): Order
    {
        /** @var Order $order */
        if ($order->isClosed()) {
            return $order;
        }

        // Quiet write + an explicit timeline entry, so this reads as
        // "Order closed" rather than a generic "Order updated".
        $order->forceFill(['closed_at' => now()])->saveQuietly();

        activity()
            ->causedBy(auth()->user())
            ->performedOn($order)
            ->event('order-closed')
            ->log('order-closed');

        OrderClosed::dispatch($order);

        return $order;
    }

    /**
     * Whether the order can be closed (it is currently open).
     */
    public static function canRun(Order $order): bool
    {
        /** @var Order $order */
        return $order->isOpen();
    }
}
