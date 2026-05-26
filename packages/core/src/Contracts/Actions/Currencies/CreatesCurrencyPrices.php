<?php

namespace Lunar\Core\Contracts\Actions\Currencies;

use Lunar\Core\Models\Contracts\Currency;

interface CreatesCurrencyPrices
{
    public function handle(Currency $incomingCurrency, Currency $baseCurrency): void;
}
