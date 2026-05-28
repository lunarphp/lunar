<?php

namespace Lunar\Core\Observers;

use Lunar\Core\Events\Orders\OrderStatusUpdated;
use Lunar\Core\Models\Contracts\Order as OrderContract;
use Lunar\Core\Models\Order;
use Lunar\Core\States\Order\OrderState;

class OrderObserver
{
    public function updating(OrderContract $order): void
    {
        /** @var Order $order */
        if ($order->isDirty('order_status')) {
            activity()
                ->causedBy(auth()->user())
                ->performedOn($order)
                ->event('status-update')
                ->withProperties([
                    'new' => (string) $order->order_status,
                    'previous' => $order->getOriginal('order_status'),
                ])
                ->log('status-update');
        }
    }

    public function updated(OrderContract $order): void
    {
        /** @var Order $order */
        if ($order->wasChanged('payment_status') || $order->wasChanged('fulfilment_status')) {
            // computeOrderStatus() uses saveQuietly() internally and dispatches
            // OrderStatusUpdated itself — do not also check wasChanged('order_status').
            $order->computeOrderStatus();

            return;
        }

        if ($order->wasChanged('order_status')) {
            OrderStatusUpdated::dispatch(
                $order,
                $order->getOriginal('order_status'),
                (string) $order->order_status,
            );

            // Leaving a manual-override state resumes automated computation.
            $previousWasOverride = $this->isManualOverrideName($order->getOriginal('order_status'));

            if ($previousWasOverride && ! $order->order_status->isManualOverride()) {
                $order->computeOrderStatus();
            }
        }
    }

    private function isManualOverrideName(?string $name): bool
    {
        if (! $name) {
            return false;
        }

        $class = OrderState::resolveStateClass($name);

        if (! $class || ! class_exists($class)) {
            return false;
        }

        return (new $class(new Order))->isManualOverride();
    }
}
