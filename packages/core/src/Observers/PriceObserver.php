<?php

namespace Lunar\Observers;

use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Lunar\Jobs\Currencies\SyncPriceCurrencies;
use Lunar\Models\Contracts\Price;
use Lunar\Models\Currency;

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
