<?php

namespace Lunar\Core\Listeners;

use Lunar\Core\Events\Orders\OrderFulfilmentStatusUpdated;

/**
 * Dispatch any notifications configured for the order's new derived fulfilment
 * status. Keyed by the state `$name` under `lunar.orders.notifications`.
 */
class SendOrderFulfilmentStatusNotifications
{
    public function handle(OrderFulfilmentStatusUpdated $event): void
    {
        $notifications = (array) config('lunar.orders.notifications.'.$event->newStatus::$name, []);

        foreach ($notifications as $class) {
            $event->order->notify(new $class($event->order));
        }
    }
}
