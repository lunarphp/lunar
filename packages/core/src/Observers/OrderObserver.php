<?php

namespace Lunar\Core\Observers;

use Lunar\Core\Events\Orders\OrderStatusUpdated;
use Lunar\Core\Models\Contracts\Order as OrderContract;
use Lunar\Core\Models\Order;

class OrderObserver
{
    public function updating(OrderContract $order): void
    {
        /** @var Order $order */
        if ($order->isDirty('status')) {
            activity()
                ->causedBy(auth()->user())
                ->performedOn($order)
                ->event('status-update')
                ->withProperties([
                    'new' => (string) $order->status,
                    'previous' => (string) $order->getOriginal('status'),
                ])
                ->log('status-update');
        }
    }

    public function updated(OrderContract $order): void
    {
        /** @var Order $order */
        if ($order->wasChanged('status')) {
            OrderStatusUpdated::dispatch(
                $order,
                $order->getOriginal('status'),
                $order->status,
            );
        }
    }
}
