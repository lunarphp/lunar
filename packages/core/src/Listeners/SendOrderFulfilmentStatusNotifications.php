<?php

namespace Lunar\Core\Listeners;

use Lunar\Core\Contracts\OrderNotificationManifest;
use Lunar\Core\Enums\NotificationScope;
use Lunar\Core\Events\Orders\OrderFulfilmentStatusUpdated;

/**
 * Dispatch any notifications registered to fire when the order enters its new
 * derived fulfilment status, when the operation that changed it asked for the
 * customer to be notified. Looked up by the state `$name` in the order-scoped
 * {@see OrderNotificationManifest}.
 */
class SendOrderFulfilmentStatusNotifications
{
    public function __construct(
        protected OrderNotificationManifest $notifications,
    ) {}

    public function handle(OrderFulfilmentStatusUpdated $event): void
    {
        if (! $event->notify) {
            return;
        }

        foreach ($this->notifications->triggeredBy($event->newStatus::$name, NotificationScope::Order) as $class) {
            $event->order->notify(new $class($event->order));
        }
    }
}
