<?php

namespace Lunar\Core\Listeners;

use Lunar\Core\Events\Orders\OrderCancelled;
use Lunar\Core\Events\Orders\OrderPlaced;
use Lunar\Core\Listeners\Concerns\SyncsTrackedStock;

/**
 * Order placed → commit its stock globally. Order cancelled → release it.
 * Both are a recompute from the order book, so one handler covers both.
 */
class SyncStockForOrder
{
    use SyncsTrackedStock;

    public function handle(OrderPlaced|OrderCancelled $event): void
    {
        $this->syncTrackedStockForOrder($event->order);
    }
}
