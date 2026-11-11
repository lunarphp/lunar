<?php

namespace Lunar\Core\Listeners;

use Lunar\Core\Contracts\FulfilmentStateConfig;
use Lunar\Core\Contracts\OrderNotificationManifest;
use Lunar\Core\Events\Fulfilment\FulfilmentStatusUpdated;
use Lunar\Core\Listeners\Concerns\SendsCustomerNotifications;

/**
 * Dispatch the per-fulfilment notifications configured for a fulfilment's new
 * state — the "your parcel has shipped, here's the tracking" path. Resolution
 * goes through the method-aware {@see FulfilmentStateConfig::notificationsFor()}
 * seam, so a custom fulfilment method maps its own states to notifications.
 *
 * Each notification is instantiated with the **Fulfilment** (so it can read the
 * fulfilment's tracking and lines) and delivered to the customer via the order's
 * existing `Notifiable` routing. Suppressed when the triggering operation asked
 * not to notify.
 */
class SendFulfilmentStatusNotifications
{
    use SendsCustomerNotifications;

    public function __construct(
        protected FulfilmentStateConfig $config,
        protected OrderNotificationManifest $notifications,
    ) {}

    public function handle(FulfilmentStatusUpdated $event): void
    {
        if (! $event->notify) {
            return;
        }

        $this->sendAfterCommit($event->fulfilment->order, $this->config->notificationsFor($event->newStatus), $event->fulfilment);
    }
}
