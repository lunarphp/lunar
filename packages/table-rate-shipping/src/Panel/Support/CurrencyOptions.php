<?php

namespace Lunar\Shipping\Panel\Support;

use Lunar\Core\Models\Currency;

/**
 * The enabled currencies as the panel screens receive them: default first,
 * with the decimal places each money input needs for its step.
 */
class CurrencyOptions
{
    /**
     * @return array<int, array{id: int, code: string, name: string, decimal_places: int, default: bool}>
     */
    public function all(): array
    {
        return Currency::query()
            ->whereEnabled(true)
            ->orderByDesc('default')
            ->orderBy('code')
            ->get()
            ->map(fn (Currency $currency) => [
                'id' => $currency->id,
                'code' => $currency->code,
                'name' => $currency->name,
                'decimal_places' => (int) $currency->decimal_places,
                'default' => (bool) $currency->default,
            ])
            ->all();
    }
}
