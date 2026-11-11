<?php

namespace Lunar\Core\Listeners\Concerns;

use Lunar\Core\Contracts\OrderNotificationManifest;
use Lunar\Core\Models\Fulfilment;
use Lunar\Core\Models\Order;

/**
 * @property OrderNotificationManifest $notifications
 */
trait SendsCustomerNotifications
{
    /**
     * Send once the surrounding transaction commits, so a rollback sends
     * nothing and the email reads committed state (tracking, payment status)
     * from freshly loaded models. Draft orders and muted runs send nothing;
     * the mute is read now, when the event fires.
     *
     * @param  array<int, class-string>  $classes
     */
    protected function sendAfterCommit(Order $order, array $classes, ?Fulfilment $fulfilment = null): void
    {
        if ($classes === [] || ! $order->isPlaced() || $this->notifications->isMuted()) {
            return;
        }

        $order->getConnection()->afterCommit(function () use ($order, $classes, $fulfilment) {
            $order = $order->fresh();
            $subject = $fulfilment ? $fulfilment->fresh() : $order;

            if (! $order || ! $subject) {
                return;
            }

            foreach ($classes as $class) {
                $order->notify(new $class($subject));
            }
        });
    }
}
