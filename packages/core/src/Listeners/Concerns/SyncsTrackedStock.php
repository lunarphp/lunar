<?php

namespace Lunar\Core\Listeners\Concerns;

use Lunar\Core\Contracts\TracksStock;
use Lunar\Core\Models\Contracts\Order as OrderContract;

trait SyncsTrackedStock
{
    /**
     * Resync committed stock for every distinct stock-tracked purchasable on an
     * order. Purchasables that do not track stock (gift cards, digital, …) are
     * skipped; the resync is idempotent, so syncing the whole order is safe.
     */
    protected function syncTrackedStockForOrder(OrderContract $order): void
    {
        // Only fulfillable lines can hold stock; this also skips shipping lines,
        // whose purchasable morph is a non-Eloquent value object.
        $order->lines()->where('requires_fulfilment', true)->with('purchasable')->get()
            ->map(fn ($line) => $line->purchasable)
            ->filter(fn ($purchasable) => $purchasable instanceof TracksStock)
            ->unique(fn ($purchasable) => $purchasable::class.':'.$purchasable->getKey())
            ->each(fn (TracksStock $purchasable) => $purchasable->syncStockCommitment());
    }
}
