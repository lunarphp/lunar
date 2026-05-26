<?php

namespace Lunar\Core\Models\Concerns;

use Illuminate\Database\Eloquent\Model;
use Lunar\Core\Contracts\HasCurrency;
use Lunar\Core\Models\Contracts\Currency;
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

    public function unitFormat(string $field, ?string $locale = null, ?int $decimalPlaces = null, bool $trimTrailingZeros = true): ?string
    {
        $value = $this->getAttribute($field);

        if ($value === null) {
            return null;
        }

        return app(PriceFormatterInterface::class, [
            'value' => (int) $value,
            'currency' => $this->getCachedCurrency(),
            'unitQty' => $this->resolveUnitQuantity(),
        ])->unitFormatted($locale, decimalPlaces: $decimalPlaces, trimTrailingZeros: $trimTrailingZeros);
    }

    public function unitDecimal(string $field, bool $rounding = true): ?float
    {
        $value = $this->getAttribute($field);

        if ($value === null) {
            return null;
        }

        return app(PriceFormatterInterface::class, [
            'value' => (int) $value,
            'currency' => $this->getCachedCurrency(),
            'unitQty' => $this->resolveUnitQuantity(),
        ])->unitDecimal($rounding);
    }

    private function getCachedCurrency(): Currency
    {
        return $this->resolvedCurrency ??= $this->resolveCurrency();
    }

    /**
     * Resolve the unit quantity for unit-aware formatting.
     *
     * Mirrors the v1 `Casts\Price::resolveUnitQuantity()` lookup: prefers a
     * loaded `priceable` relation (so `lunar_prices` rows reach their
     * variant), then the model's own `unit_quantity` column, and finally
     * `1` for models without a unit-quantity concept.
     */
    private function resolveUnitQuantity(): int
    {
        if ($this instanceof Model) {
            if ($this->relationLoaded('priceable')) {
                $priceable = $this->getRelation('priceable');

                if ($priceable !== null && isset($priceable->unit_quantity)) {
                    return (int) $priceable->unit_quantity;
                }
            }

            $attributes = $this->getAttributes();

            return (int) ($attributes['unit_quantity'] ?? 1);
        }

        return 1;
    }
}
