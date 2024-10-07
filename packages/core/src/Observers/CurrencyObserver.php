<?php

namespace Lunar\Observers;

use Lunar\Models\Contracts\Currency as CurrencyContract;
use Lunar\Models\Currency;

class CurrencyObserver
{
    /**
     * Handle the Currency "created" event.
     *
     * @return void
     */
    public function created(CurrencyContract $currency)
    {
        $this->ensureOnlyOneDefault($currency);
    }

    /**
     * Handle the Currency "updated" event.
     *
     * @return void
     */
    public function updated(CurrencyContract $currency)
    {
        $this->ensureOnlyOneDefault($currency);
    }

    /**
     * Handle the Currency "deleted" event.
     *
     * @return void
     */
    public function deleted(CurrencyContract $currency)
    {
        //
    }

    /**
     * Handle the Currency "forceDeleted" event.
     *
     * @return void
     */
    public function forceDeleted(CurrencyContract $currency)
    {
        //
    }

    /**
     * Ensures that only one default currency exists.
     *
     * @param  CurrencyContract  $savedCurrency  The currency that was just saved.
     */
    protected function ensureOnlyOneDefault(CurrencyContract $savedCurrency): void
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
