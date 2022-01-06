<?php

namespace GetCandy\Observers;

use GetCandy\Models\Channel;
use GetCandy\Models\Currency;

class CurrencyObserver
{
    /**
     * Handle the Currency "created" event.
     *
     * @param  \App\Models\Currency  $currency
     * @return void
     */
    public function created(Currency $currency)
    {
        $this->ensureOnlyOneDefault($currency);
    }

    /**
     * Handle the Currency "updated" event.
     *
     * @param  \App\Models\Currency  $currency
     * @return void
     */
    public function updated(Currency $currency)
    {
        $this->ensureOnlyOneDefault($currency);
    }

    /**
     * Handle the Currency "deleted" event.
     *
     * @param  \App\Models\Currency  $currency
     * @return void
     */
    public function deleted(Currency $currency)
    {
        //
    }

    /**
     * Handle the Currency "forceDeleted" event.
     *
     * @param  \App\Models\Currency  $currency
     * @return void
     */
    public function forceDeleted(Currency $currency)
    {
        //
    }

    /**
     * Ensures that only one default currency exists.
     *
     * @param  \GetCandy\Models\Currency  $savedCurrency  The currency that was just saved.
     * @return void
     */
    protected function ensureOnlyOneDefault(Currency $savedCurrency): void
    {
        // Wrap here so we avoid a query if it's not been set to default.
        if ($savedCurrency->default) {
            $currencies = Currency::whereDefault(true)->where('id', '!=', $savedCurrency->id)->get();

            foreach ($currencies as $currency) {
                $currency->default = false;
                $currency->saveQuietly();
            }
        }
    }
}
