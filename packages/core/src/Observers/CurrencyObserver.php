<?php

namespace Lunar\Observers;

use Lunar\Models\Currency;

class CurrencyObserver
{
    /**
     * Handle the Currency "created" event.
     *
     * @param Currency $currency
     * @return void
     */
    public function created(Currency $currency)
    {
        $this->ensureOnlyOneDefault($currency);
    }

    /**
     * Handle the Currency "updated" event.
     *
     * @param Currency $currency
     * @return void
     */
    public function updated(Currency $currency)
    {
        $this->ensureOnlyOneDefault($currency);
    }

    /**
     * Handle the Currency "saved" event.
     *
     * @param Currency $currency
     * @return void
     */
    public function saved(Currency $currency)
    {
        $this->triggerAutomaticPriceConversion($currency);
    }

    /**
     * Handle the Currency "deleted" event.
     *
     * @param Currency $currency
     * @return void
     */
    public function deleted(Currency $currency)
    {
        //
    }

    /**
     * Handle the Currency "forceDeleted" event.
     *
     * @param Currency $currency
     * @return void
     */
    public function forceDeleted(Currency $currency)
    {
        //
    }

    /**
     * Ensures that only one default currency exists.
     *
     * @param Currency $savedCurrency The currency that was just saved.
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

    /**
     * Trigger automatic price conversion job
     *
     * @param Currency $savedCurrency The currency that was just saved.
     * @return void
     */
    protected function triggerAutomaticPriceConversion(Currency $savedCurrency): void
    {
        $autoConversion = config('lunar.pricing.auto_conversion');

        if ($autoConversion['enabled'] !== true) {
            return;
        }

        // we only interested in change of non-default currency exchange rate
        if ($savedCurrency->default || !$savedCurrency->wasChanged('exchange_rate')) {
            return;
        }

        $autoConversion['currency_update_job']::dispatch($savedCurrency);
    }
}
