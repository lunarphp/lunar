<?php

namespace Lunar\Core\Listeners;

use Lunar\Core\Contracts\Actions\Fulfilment\EnsuresInitialFulfilment;
use Lunar\Core\Events\Orders\OrderPlaced;

/**
 * Give a newly-placed order its initial fulfilment — one fulfilment per claiming
 * method (the merchant splits from there). Idempotent: a no-op if the order
 * already has a fulfilment.
 */
class EnsureInitialFulfilmentForOrder
{
    public function __construct(
        protected EnsuresInitialFulfilment $ensureInitialFulfilment,
    ) {}

    public function handle(OrderPlaced $event): void
    {
        $this->ensureInitialFulfilment->execute($event->order);
    }
}
