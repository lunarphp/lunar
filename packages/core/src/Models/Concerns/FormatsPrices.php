<?php

namespace Lunar\Core\Models\Concerns;

use Illuminate\Database\Eloquent\Model;
use Lunar\Core\Contracts\HasCurrency;
use Lunar\Core\Models\Currency;
use Lunar\Core\Pricing\PriceFormatterInterface;

/**
 * @mixin Model
 *
 * @see HasCurrency
 */
trait FormatsPrices
{
    private ?Currency $resolvedCurrency = null;

    public function format(string $field, ?string $locale = null, ?int $decimalPlaces = null, bool $trimTrailingZeros = true): ?string
    {
        $value = $this->getAttribute($field);

        if ($value === null) {
            return null;
        }

        return app(PriceFormatterInterface::class, [
            'value' => (int) $value,
            'currency' => $this->getCachedCurrency(),
        ])->formatted($locale, decimalPlaces: $decimalPlaces, trimTrailingZeros: $trimTrailingZeros);
    }

    public function decimal(string $field, bool $rounding = true): ?float
    {
        $value = $this->getAttribute($field);

        if ($value === null) {
            return null;
        }

        return app(PriceFormatterInterface::class, [
            'value' => (int) $value,
            'currency' => $this->getCachedCurrency(),
        ])->decimal($rounding);
    }

    private function getCachedCurrency(): Currency
    {
        return $this->resolvedCurrency ??= $this->resolveCurrency();
    }
}
