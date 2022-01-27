<?php

namespace GetCandy\Observers;

use GetCandy\Models\Order;

class OrderObserver
{
    /**
     * Handle the Order "creating" event.
     *
     * @param \App\Models\Order $order
     *
     * @return void
     */
    public function created(Order $order)
    {
    }
}
