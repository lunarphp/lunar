<?php

namespace Lunar\Core\Listeners;

use Lunar\Core\Contracts\FulfilmentStateConfig;
use Lunar\Core\Events\Fulfilment\FulfilmentStatusUpdated;

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
    public function __construct(
        protected FulfilmentStateConfig $config,
    ) {}

    public function handle(FulfilmentStatusUpdated $event): void
    {
        if (! $event->notify) {
            return;
        }

        foreach ($this->config->notificationsFor($event->newStatus) as $class) {
            $event->fulfilment->order->notify(new $class($event->fulfilment));
        }
    }
}
