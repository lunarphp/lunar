<?php

namespace Lunar\Core\Listeners;

use Lunar\Core\Contracts\OrderNotificationManifest;
use Lunar\Core\Enums\NotificationScope;
use Lunar\Core\Events\Orders\OrderCancelled;

/**
 * Dispatch any notifications registered for a cancelled order, when the
 * cancellation asked for the customer to be notified. Looked up by the
 * `cancelled` key in the order-scoped {@see OrderNotificationManifest}.
 */
class SendOrderCancelledNotifications
{
    public function __construct(
        protected OrderNotificationManifest $notifications,
    ) {}

    public function handle(OrderCancelled $event): void
    {
        if (! $event->notify) {
            return;
        }

        foreach ($this->notifications->triggeredBy('cancelled', NotificationScope::Order) as $class) {
            $event->order->notify(new $class($event->order));
        }
    }
}
