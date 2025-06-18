<?php

namespace Lunar\Observers;

use Lunar\Jobs\Currencies\SyncPriceCurrencies;
use Lunar\Models\Contracts\Price;

class PriceObserver
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
