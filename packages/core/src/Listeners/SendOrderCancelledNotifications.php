<?php

namespace Lunar\Core\Listeners;

use Lunar\Core\Events\Orders\OrderCancelled;

/**
 * Dispatch any notifications configured for a cancelled order, when the
 * cancellation asked for the customer to be notified. Configured under
 * `lunar.orders.notifications.cancelled`.
 */
class SendOrderCancelledNotifications
{
    public function handle(OrderCancelled $event): void
    {
        if (! $event->notify) {
            return;
        }

        $notifications = (array) config('lunar.orders.notifications.cancelled', []);

        foreach ($notifications as $class) {
            $event->order->notify(new $class($event->order));
        }
    }
}
