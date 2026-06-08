<?php

namespace Lunar\Core\Actions\Orders;

use Lunar\Core\Contracts\Actions\Orders\MarksOrderAsComplete;
use Lunar\Core\Models\Contracts\Order as OrderContract;
use Lunar\Core\Models\Order;
use Lunar\Core\States\Order\Order\Complete;

/**
 * Manually close a shipped order. `Complete` is never derived (there is no
 * delivery signal in core), so it is only ever reached through this guarded
 * transition.
 */
final class MarkOrderAsComplete implements MarksOrderAsComplete
{
    public function execute(OrderContract $order): Order
    {
        /** @var Order $order */
        $order->status->transitionTo(Complete::class);

        return $order->refresh();
    }

    /**
     * Whether the order can be marked complete, per the order state graph.
     */
    public static function canRun(OrderContract $order): bool
    {
        /** @var Order $order */
        return $order->status->canTransitionTo(Complete::class);
    }
}
