<?php

namespace Lunar\Core\Listeners;

use Lunar\Core\Events\Orders\OrderFulfilmentStatusUpdated;

/**
 * Dispatch any notifications configured for the order's new derived fulfilment
 * status, when the operation that changed it asked for the customer to be
 * notified. Keyed by the state `$name` under `lunar.orders.notifications`.
 */
class SendOrderFulfilmentStatusNotifications
{
    public function handle(OrderFulfilmentStatusUpdated $event): void
    {
        if (! $event->notify) {
            return;
        }

        $notifications = (array) config('lunar.orders.notifications.'.$event->newStatus::$name, []);

        foreach ($notifications as $class) {
            $event->order->notify(new $class($event->order));
        }
    }
}
