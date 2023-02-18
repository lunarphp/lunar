<?php

namespace Lunar\Observers;

use Lunar\Models\Price;

class PriceObserver
{

    /**
     * Handle the Price "saved" event.
     *
     * @param Price $price
     * @return void
     */
    public function saved(Price $price)
    {
        $this->triggerAutomaticPriceConversion($price);
    }

    /**
     * Trigger automatic price conversion job
     *
     * @param Price $savedPrice  The price that was just saved.
     * @return void
     */
    protected function triggerAutomaticPriceConversion(Price $savedPrice): void
    {
        $autoConversion = config('lunar.pricing.auto_conversion');

        if ($autoConversion['enabled'] !== true) {
            return;
        }

        // we only interested in change of price in default currency
        if (!$savedPrice->currency->default || !$savedPrice->wasChanged('price')) {
            return;
        }

        $autoConversion['price_update_job']::dispatch($savedPrice)
            ->onConnection($autoConversion['connection'])
            ->onQueue($autoConversion['queue']);
    }
}
