<?php

namespace Lunar\Core\Listeners;

use Lunar\Core\Contracts\OrderNotificationManifest;
use Lunar\Core\Enums\NotificationScope;
use Lunar\Core\Events\Orders\OrderPaymentStatusUpdated;

/**
 * Dispatch any notifications registered to fire when the order enters its new
 * derived payment status, looked up by the state `$name` in the order-scoped
 * {@see OrderNotificationManifest}.
 *
 * Only placed orders are emailed. A card checkout records its capture before
 * `placed_at` is stamped, so the `paid` rollup fires on an unplaced order and is
 * skipped here — the confirmation that follows carries the payment state. A
 * later capture on a placed order (authorize-then-capture, an offline payment
 * marked paid) does email. Draft orders never email a customer.
 */
class SendOrderPaymentStatusNotifications
{
    public function __construct(
        protected OrderNotificationManifest $notifications,
    ) {}

    public function handle(OrderPaymentStatusUpdated $event): void
    {
        if (! $event->order->isPlaced()) {
            return;
        }

        foreach ($this->notifications->triggeredBy($event->newStatus::$name, NotificationScope::Order) as $class) {
            $event->order->notify(new $class($event->order));
        }
    }
}
