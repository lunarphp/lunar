<?php

namespace GetCandy\Observers;

use GetCandy\Models\Order;

class OrderObserver
{
    /**
     * Handle the OrderLine "updated" event.
     *
     * @param  \GetCandy\Models\OrderLine  $orderLine
     * @return void
     */
    public function updating(Order $order)
    {
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
