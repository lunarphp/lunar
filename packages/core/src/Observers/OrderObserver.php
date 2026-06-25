<?php

namespace Lunar\Core\Observers;

use Lunar\Core\Contracts\Actions\Fulfilment\EnsuresInitialFulfilment;
use Lunar\Core\Models\Contracts\Order as OrderContract;
use Lunar\Core\Models\Order;

class OrderObserver
{
    public function __construct(
        protected EnsuresInitialFulfilment $ensureInitialFulfilment,
    ) {}

    public function created(OrderContract $order): void
    {
        /** @var Order $order */
        if ($order->isPlaced()) {
            $this->ensureInitialFulfilment->execute($order);
        }
    }

    public function updated(OrderContract $order): void
    {
        /** @var Order $order */

        // The moment an order is placed it gets its initial fulfilment — one
        // fulfilment covering every physical line (the merchant splits from there).
        if ($order->wasChanged('placed_at') && $order->isPlaced()) {
            $this->ensureInitialFulfilment->execute($order);
        }
    }
}
