<?php

namespace Lunar\Core\Observers;

use Lunar\Core\Contracts\Actions\Fulfilment\EnsuresInitialFulfilment;
use Lunar\Core\Events\Orders\OrderPlaced;
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
            $this->onPlaced($order);
        }
    }

    public function updated(OrderContract $order): void
    {
        /** @var Order $order */
        if ($order->wasChanged('placed_at') && $order->isPlaced()) {
            $this->onPlaced($order);
        }
    }

    /**
     * The moment an order is placed it commits its stock and gets its initial
     * fulfilment — one fulfilment per claiming method (the merchant splits from there).
     */
    protected function onPlaced(Order $order): void
    {
        OrderPlaced::dispatch($order);

        $this->ensureInitialFulfilment->execute($order);
    }
}
