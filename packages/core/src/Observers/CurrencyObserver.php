<?php

namespace Lunar\Observers;

use Lunar\Models\Currency;

class CurrencyObserver
{
    /**
     * Handle the Currency "created" event.
     *
     * @return void
     */
    public function created(Currency $currency)
    {
        $this->ensureOnlyOneDefault($currency);
    }

    /**
     * Handle the Currency "updated" event.
     *
     * @return void
     */
    public function updated(Currency $currency)
    {
        $this->ensureOnlyOneDefault($currency);
    }

    /**
     * Handle the Currency "deleted" event.
     *
     * @return void
     */
    public function deleted(Currency $currency)
    {
        //
    }

    /**
     * Handle the Currency "forceDeleted" event.
     *
     * @return void
     */
    public function forceDeleted(Currency $currency)
    {
        //
    }

    /**
     * Ensures that only one default currency exists.
     *
     * @param  \Lunar\Models\Currency  $savedCurrency  The currency that was just saved.
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
