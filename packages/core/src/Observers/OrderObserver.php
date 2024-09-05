<?php

namespace Lunar\Observers;

use Lunar\Models\Contracts\Order as OrderContract;
use Lunar\Models\Order;

class OrderObserver
{
    /**
     * Handle the Order "updating" event.
     *
     * @return void
     */
    public function updating(OrderContract $order)
    {
        /** @var Order $order */
        if ($order->getOriginal('status') != $order->status) {
            activity()
                ->causedBy(auth()->user())
                ->performedOn($order)
                ->event('status-update')
                ->withProperties([
                    'new' => $order->status,
                    'previous' => $order->getOriginal('status'),
                ])->log('status-update');
        }
    }
}
