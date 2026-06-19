<?php

namespace Lunar\Core\Listeners;

use Lunar\Core\Contracts\OrderNotificationManifest;
use Lunar\Core\Enums\NotificationScope;
use Lunar\Core\Events\Orders\OrderPaymentStatusUpdated;

/**
 * Dispatch any notifications registered to fire when the order enters its new
 * derived payment status, looked up by the state `$name` in the order-scoped
 * {@see OrderNotificationManifest}.
 */
class SendOrderPaymentStatusNotifications
{
    public function __construct(
        protected OrderNotificationManifest $notifications,
    ) {}

    public function handle(OrderPaymentStatusUpdated $event): void
    {
        foreach ($this->notifications->triggeredBy($event->newStatus::$name, NotificationScope::Order) as $class) {
            $event->order->notify(new $class($event->order));
        }
    }
}
