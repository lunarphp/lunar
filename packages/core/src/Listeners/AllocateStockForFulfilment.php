<?php

namespace Lunar\Core\Listeners;

use Lunar\Core\Events\Fulfilment\FulfilmentCreated;
use Lunar\Core\Listeners\Concerns\SyncsTrackedStock;

/**
 * A fulfilment was created → allocate its lines' commitment to the fulfilment's
 * location. The recompute reads the outstanding fulfilment lines, so allocation
 * falls out of resyncing the order's tracked purchasables.
 */
class AllocateStockForFulfilment
{
    use SyncsTrackedStock;

    public function handle(FulfilmentCreated $event): void
    {
        $this->syncTrackedStockForOrder($event->fulfilment->loadMissing('order')->order);
    }
}
