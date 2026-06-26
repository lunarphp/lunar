<?php

namespace Lunar\Core\Observers;

use Lunar\Core\Events\Orders\OrderPlaced;
use Lunar\Core\Models\Contracts\Order as OrderContract;
use Lunar\Core\Models\Order;

class OrderObserver
{
    /**
     * Announce a newly-placed order. Reactions — its initial fulfilment, stock
     * commitment — listen on {@see OrderPlaced}.
     */
    public function created(OrderContract $order): void
    {
        /** @var Order $order */
        if ($order->isPlaced()) {
            OrderPlaced::dispatch($order);
        }
    }

    public function updated(OrderContract $order): void
    {
        /** @var Order $order */
        if ($order->wasChanged('placed_at') && $order->isPlaced()) {
            OrderPlaced::dispatch($order);
        }
    }
}
