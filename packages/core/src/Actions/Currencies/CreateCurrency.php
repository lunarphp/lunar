<?php

namespace Lunar\Core\Actions\Currencies;

use Lunar\Core\Contracts\Actions\Currencies\CreatesCurrency;
use Lunar\Core\Models\Currency;

/**
 * Create a currency, ensuring at most one currency is ever marked default.
 * The default currency is always enabled — a store cannot transact in a
 * disabled default.
 */
class CreateCurrency implements CreatesCurrency
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(array $attributes): Currency
    {
        if ($attributes['default'] ?? false) {
            $attributes['enabled'] = true;

            Currency::query()->where('default', true)->update(['default' => false]);
        }

        return Currency::create($attributes);
    }
}
