<?php

namespace Lunar\Core\Actions\Currencies;

use Lunar\Core\Contracts\Actions\Currencies\DeletesCurrency;
use Lunar\Core\Exceptions\CurrencyActionException;
use Lunar\Core\Models\Currency;

/**
 * Delete a currency. Currencies with prices are kept — disable them instead —
 * so existing pricing keeps its context. The default currency is also kept:
 * make another currency the default first.
 */
class DeleteCurrency implements DeletesCurrency
{
    public function execute(Currency $currency): void
    {
        if ($currency->default) {
            throw new CurrencyActionException('Cannot delete the default currency. Make another currency the default first.');
        }

        if ($currency->prices()->exists()) {
            throw new CurrencyActionException('Cannot delete a currency with prices.');
        }

        $currency->delete();
    }
}
