<?php

namespace Lunar\Core\Pricing;

use NumberFormatter;

interface PriceFormatterInterface
{
    public function decimal(bool $rounding = true): float;

    public function unitDecimal(bool $rounding = true): float;

    public function formatted(?string $locale = null, int $formatterStyle = NumberFormatter::CURRENCY, ?int $decimalPlaces = null, bool $trimTrailingZeros = true): mixed;

    public function unitFormatted(?string $locale = null, int $formatterStyle = NumberFormatter::CURRENCY, ?int $decimalPlaces = null, bool $trimTrailingZeros = true): mixed;
}
