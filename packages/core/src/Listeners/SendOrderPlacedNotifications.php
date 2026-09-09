<?php

namespace Lunar\Core\Listeners;

use Lunar\Core\Contracts\OrderNotificationManifest;
use Lunar\Core\Enums\NotificationScope;
use Lunar\Core\Events\Orders\OrderPlaced;

/**
 * Dispatch any notifications registered for the `placed` key when an order is
 * placed — the order confirmation path. There is no `notify` flag: placement is
 * customer-initiated, so the confirmation always goes out.
 */
class SendOrderPlacedNotifications
{
    public function __construct(
        protected OrderNotificationManifest $notifications,
    ) {}

    public function handle(OrderPlaced $event): void
    {
        foreach ($this->notifications->triggeredBy('placed', NotificationScope::Order) as $class) {
            $event->order->notify(new $class($event->order));
        }
    }
}
