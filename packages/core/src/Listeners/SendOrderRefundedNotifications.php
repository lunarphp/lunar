<?php

namespace Lunar\Core\Listeners;

use Lunar\Core\Contracts\OrderNotificationManifest;
use Lunar\Core\Enums\NotificationScope;
use Lunar\Core\Events\Orders\OrderRefunded;
use Lunar\Core\Listeners\Concerns\SendsCustomerNotifications;

/**
 * Dispatch any notifications registered for a refunded order, when the
 * refund asked for the customer to be notified. Looked up by the `refund-issued`
 * key in the order-scoped {@see OrderNotificationManifest} — deliberately not
 * `refunded`, which is the payment-status rollup's (ungated) key and would
 * otherwise fire the same notification twice on a full refund.
 */
class SendOrderRefundedNotifications
{
    use SendsCustomerNotifications;

    public function __construct(
        protected OrderNotificationManifest $notifications,
    ) {}

    public function handle(OrderRefunded $event): void
    {
        if (! $event->notify) {
            return;
        }

        $this->sendAfterCommit($event->order, $this->notifications->triggeredBy('refund-issued', NotificationScope::Order));
    }
}
