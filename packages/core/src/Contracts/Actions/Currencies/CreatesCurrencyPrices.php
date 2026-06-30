<?php

namespace Lunar\Core\Contracts\Actions\Currencies;

use Lunar\Core\Models\Currency;

interface CreatesCurrencyPrices
{
    public function execute(Currency $incomingCurrency, Currency $baseCurrency): void;
}
