<?php

namespace Lunar\Core\Actions\Orders;

use Lunar\Core\Contracts\Actions\Orders\UpdatesOrderStatus;
use Lunar\Core\Contracts\OrderStateConfig;
use Lunar\Core\Exceptions\OrderActionException;
use Lunar\Core\Models\Contracts\Order as OrderContract;
use Lunar\Core\Models\Order;

/**
 * Apply an order_status change to an order.
 *
 * Canonical entry point for direct order_status mutation (typically manual
 * overrides like OnHold / Cancelled, or resuming back to a computed state).
 * For payment/fulfilment changes, mutate $order->payment_status /
 * $order->fulfilment_status — the order_status is recomputed automatically.
 */
final class UpdateOrderStatus implements UpdatesOrderStatus
{
    public function __construct(
        private OrderStateConfig $stateConfig,
    ) {}

    public function execute(OrderContract $order, string $status): Order
    {
        /** @var Order $order */
        $allowed = collect($this->stateConfig->orderStates())
            ->map(fn (string $class) => $class::getMorphClass())
            ->all();

        if (! in_array($status, $allowed, true)) {
            throw new OrderActionException(
                "Status [{$status}] is not a registered OrderState."
            );
        }

        $previous = (string) $order->order_status;

        if ($previous === $status) {
            return $order;
        }

        $order->forceFill(['order_status' => $status])->save();

        return $order;
    }
}
