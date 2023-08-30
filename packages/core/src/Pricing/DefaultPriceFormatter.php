<?php

namespace Lunar\Pricing;

use Illuminate\Support\Facades\App;
use Lunar\Models\Currency;
use NumberFormatter;

class DefaultPriceFormatter implements PriceFormatterInterface
{
    public function __construct(
        public int $value,
        public ?Currency $currency = null,
        public int $unitQty = 1
    ) {
        if (! $this->currency) {
            $this->currency = Currency::getDefault();
        }
    }

    public function decimal(bool $rounding = true): float
    {
        $convertedValue = $this->value / $this->currency->factor;

        return $rounding ? round($convertedValue, $this->currency->decimal_places) : $convertedValue;
    }

    public function unitDecimal(bool $rounding = true): float
    {
        $convertedValue = $this->value / $this->currency->factor / $this->unitQty;

        return $rounding ? round($convertedValue, $this->currency->decimal_places) : $convertedValue;
    }

    public function formatted(string $locale = null, string $formatterStyle = NumberFormatter::CURRENCY, int $decimalPlaces = null, bool $trimTrailingZeros = true): mixed
    {
        return $this->formatValue($this->decimal(false), $locale, $formatterStyle, $decimalPlaces, $trimTrailingZeros);
    }

    public function unitFormatted(string $locale = null, string $formatterStyle = NumberFormatter::CURRENCY, int $decimalPlaces = null, bool $trimTrailingZeros = true): mixed
    {
        return $this->formatValue($this->unitDecimal(false), $locale, $formatterStyle, $decimalPlaces, $trimTrailingZeros);
    }

    protected function formatValue(int|float $value, string $locale = null, string $formatterStyle = NumberFormatter::CURRENCY, int $decimalPlaces = null, bool $trimTrailingZeros = true): mixed
    {
        if (! $locale) {
            $locale = App::currentLocale();
        }

        $formatter = new NumberFormatter($locale, $formatterStyle);

        $formatter->setTextAttribute(NumberFormatter::CURRENCY_CODE, $this->currency->code);
        $formatter->setAttribute(NumberFormatter::FRACTION_DIGITS, $decimalPlaces ?? $this->currency->decimal_places);

        $formattedPrice = $formatter->format($value);

        if ($trimTrailingZeros) {
            $formattedPrice = preg_replace('/(\.\d{'.$this->currency->decimal_places.'}\d*?)0+$/', '$1', $formattedPrice);
        }

        return $formattedPrice;
    }
}
