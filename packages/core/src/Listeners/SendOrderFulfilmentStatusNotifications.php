<?php

namespace Lunar\Core\Listeners;

use Lunar\Core\Contracts\OrderNotificationManifest;
use Lunar\Core\Enums\NotificationScope;
use Lunar\Core\Events\Orders\OrderFulfilmentStatusUpdated;
use Lunar\Core\Listeners\Concerns\SendsCustomerNotifications;

/**
 * Dispatch any notifications registered to fire when the order enters its new
 * derived fulfilment status, when the operation that changed it asked for the
 * customer to be notified. Looked up by the state `$name` in the order-scoped
 * {@see OrderNotificationManifest}. Draft (unplaced) orders never email.
 */
class SendOrderFulfilmentStatusNotifications
{
    use SendsCustomerNotifications;

    public function __construct(
        protected OrderNotificationManifest $notifications,
    ) {}

    public function handle(OrderFulfilmentStatusUpdated $event): void
    {
        if (! $event->notify) {
            return;
        }

        $this->sendAfterCommit($event->order, $this->notifications->triggeredBy($event->newStatus::$name, NotificationScope::Order));
    }
}
