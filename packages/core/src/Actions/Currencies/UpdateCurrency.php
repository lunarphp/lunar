<?php

namespace Lunar\Core\Actions\Currencies;

use Lunar\Core\Contracts\Actions\Currencies\UpdatesCurrency;
use Lunar\Core\Exceptions\CurrencyActionException;
use Lunar\Core\Models\Currency;

/**
 * Update a currency, ensuring at most one currency is ever marked default.
 * The default flag moves by promoting another currency, never by unsetting —
 * so a store with currencies always has a default. The default currency
 * cannot be disabled either; promote another currency first.
 */
class UpdateCurrency implements UpdatesCurrency
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(Currency $currency, array $attributes): Currency
    {
        if ($currency->default && array_key_exists('default', $attributes) && ! $attributes['default']) {
            throw new CurrencyActionException('Cannot unset the default currency. Make another currency the default instead.');
        }

        $willBeDefault = (bool) ($attributes['default'] ?? $currency->default);

        if ($willBeDefault && array_key_exists('enabled', $attributes) && ! $attributes['enabled']) {
            throw new CurrencyActionException('Cannot disable the default currency. Make another currency the default first.');
        }

        if ($attributes['default'] ?? false) {
            $attributes['enabled'] = true;

            Currency::query()->where('default', true)->where('id', '!=', $currency->id)->update(['default' => false]);
        }

        $currency->update($attributes);

        return $currency;
    }
}
