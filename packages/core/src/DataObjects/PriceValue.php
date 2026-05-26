<?php

namespace Lunar\Core\DataObjects;

use Lunar\Core\Contracts\HasCurrency;
use Lunar\Core\Models\Concerns\FormatsPrices;
use Lunar\Core\Models\Contracts\Currency;

class PriceValue implements HasCurrency
{
    use FormatsPrices {
        format as private traitFormat;
        decimal as private traitDecimal;
        unitFormat as private;
        unitDecimal as private;
    }

    public function __construct(
        public readonly int $value,
        private readonly Currency $currency,
    ) {}

    public function getAttribute(string $key): mixed
    {
        return $this->{$key};
    }

    public function resolveCurrency(): Currency
    {
        return $this->currency;
    }

    public function format(?string $locale = null, ?int $decimalPlaces = null, bool $trimTrailingZeros = true): ?string
    {
        return $this->traitFormat('value', $locale, $decimalPlaces, $trimTrailingZeros);
    }

    public function decimal(bool $rounding = true): ?float
    {
        return $this->traitDecimal('value', $rounding);
    }
}
