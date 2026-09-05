<?php

namespace Lunar\Core\Listeners;

use Lunar\Core\Contracts\OrderNotificationManifest;
use Lunar\Core\Enums\NotificationScope;
use Lunar\Core\Events\Orders\OrderRefunded;

/**
 * Dispatch any notifications registered for a refunded order, when the
 * refund asked for the customer to be notified. Looked up by the `refunded`
 * key in the order-scoped {@see OrderNotificationManifest}.
 */
class SendOrderRefundedNotifications
{
    public function __construct(
        protected OrderNotificationManifest $notifications,
    ) {}

    public function handle(OrderRefunded $event): void
    {
        if (! $event->notify) {
            return;
        }

        foreach ($this->notifications->triggeredBy('refunded', NotificationScope::Order) as $class) {
            $event->order->notify(new $class($event->order));
        }
    }
}
