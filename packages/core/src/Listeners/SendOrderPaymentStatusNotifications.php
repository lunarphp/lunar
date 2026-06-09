<?php

namespace Lunar\Core\Listeners;

use Lunar\Core\Events\Orders\OrderPaymentStatusUpdated;

/**
 * Dispatch any notifications configured for the order's new derived payment
 * status. Keyed by the state `$name` under `lunar.orders.notifications`.
 */
class SendOrderPaymentStatusNotifications
{
    public function handle(OrderPaymentStatusUpdated $event): void
    {
        $notifications = (array) config('lunar.orders.notifications.'.$event->newStatus::$name, []);

        foreach ($notifications as $class) {
            $event->order->notify(new $class($event->order));
        }
    }
}
