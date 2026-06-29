<?php

namespace Lunar\Core\Observers;

use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Lunar\Core\Jobs\Currencies\SyncPriceCurrencies;
use Lunar\Core\Models\Currency;
use Lunar\Core\Models\Price;

class PriceObserver implements ShouldHandleEventsAfterCommit
{
    public function created(Price $price): void
    {
        if ($price->currency->default && $this->hasCurrenciesToSync($price)) {
            SyncPriceCurrencies::dispatch($price);
        }
    }

    public function updated(Price $price): void
    {
        if ($price->currency->default && $this->hasCurrenciesToSync($price)) {
            SyncPriceCurrencies::dispatch($price);
        }
    }

    protected function hasCurrenciesToSync(Price $price): bool
    {
        return Currency::query()
            ->where('id', '!=', $price->currency_id)
            ->where('sync_prices', true)
            ->exists();
    }
}
