<?php

namespace Lunar\Core\Listeners;

use Lunar\Core\Events\Orders\OrderStatusUpdated;

class SendOrderStatusNotifications
{
    public function handle(OrderStatusUpdated $event): void
    {
        /** @var array<class-string> $notifications */
        $notifications = config(
            "lunar.sales.orders.statuses.{$event->newStatus}.notifications",
            []
        );

        foreach ($notifications as $class) {
            $event->order->notify(new $class($event->order));
        }
    }
}
