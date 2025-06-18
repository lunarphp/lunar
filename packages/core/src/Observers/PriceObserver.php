<?php

namespace Lunar\Observers;

use Lunar\Jobs\Currencies\SyncPriceCurrencies;
use Lunar\Models\Contracts\Price;

class PriceObserver
{
    public function updated(Price $price): void
    {
        if ($price->currency->default) {
            SyncPriceCurrencies::dispatchSync($price);
        }
    }
}
