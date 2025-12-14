<?php

namespace Lunar\Observers;

use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Lunar\Jobs\Currencies\SyncPriceCurrencies;
use Lunar\Models\Contracts\Price;

class PriceObserver implements ShouldHandleEventsAfterCommit
{
    public function created(Price $price): void
    {
        if ($price->currency->default) {
            SyncPriceCurrencies::dispatch($price);
        }
    }

    public function updated(Price $price): void
    {
        if ($price->currency->default) {
            SyncPriceCurrencies::dispatch($price);
        }
    }
}
