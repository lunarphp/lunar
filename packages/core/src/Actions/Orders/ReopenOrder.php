<?php

namespace Lunar\Core\Actions\Orders;

use Lunar\Core\Contracts\Actions\Orders\ReopensOrder;
use Lunar\Core\Events\Orders\OrderReopened;
use Lunar\Core\Models\Contracts\Order as OrderContract;
use Lunar\Core\Models\Order;

/**
 * Reopen (un-archive) a closed order, clearing `closed_at` so it returns to
 * the open work queue. Idempotent — reopening an open order is a no-op.
 */
class ReopenOrder implements ReopensOrder
{
    public function execute(OrderContract $order): Order
    {
        /** @var Order $order */
        if ($order->isOpen()) {
            return $order;
        }

        $order->forceFill(['closed_at' => null])->saveQuietly();

        activity()
            ->causedBy(auth()->user())
            ->performedOn($order)
            ->event('order-reopened')
            ->log('order-reopened');

        OrderReopened::dispatch($order);

        return $order;
    }

    /**
     * Whether the order can be reopened (it is currently closed).
     */
    public static function canRun(OrderContract $order): bool
    {
        /** @var Order $order */
        // A cancelled order is closed but must not be reopened (cancellation is
        // one-way).
        return $order->isClosed() && ! $order->isCancelled();
    }
}
