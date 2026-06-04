<?php

namespace Lunar\Core\Actions\Orders;

use Lunar\Core\Contracts\Actions\Orders\UpdatesOrderStatus;
use Lunar\Core\Contracts\OrderStateConfig;
use Lunar\Core\Exceptions\OrderActionException;
use Lunar\Core\Models\Contracts\Order as OrderContract;
use Lunar\Core\Models\Order;

/**
 * Apply a status change to an order, respecting the OrderState transition graph.
 */
final class UpdateOrderStatus implements UpdatesOrderStatus
{
    public function __construct(
        private OrderStateConfig $stateConfig,
    ) {}

    public function execute(OrderContract $order, string $status): Order
    {
        /** @var Order $order */
        $target = collect($this->stateConfig->orderStates())
            ->first(fn (string $class) => $class::getMorphClass() === $status);

        if (! $target) {
            throw new OrderActionException(
                "Status [{$status}] is not a registered OrderState."
            );
        }

        if ((string) $order->status === $status) {
            return $order;
        }

        $order->status->transitionTo($target);

        return $order->refresh();
    }
}
