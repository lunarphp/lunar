<?php

namespace Lunar\Core\DataObjects;

use Lunar\Core\Contracts\HasCurrency;
use Lunar\Core\Exceptions\MismatchedCurrencyException;
use Lunar\Core\Models\Concerns\FormatsPrices;
use Lunar\Core\Models\Currency;

class PriceValue implements HasCurrency
{
    use FormatsPrices {
        format as private traitFormat;
        decimal as private traitDecimal;
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

    /**
     * Add another PriceValue. Throws if currencies differ.
     */
    public function add(PriceValue $other): PriceValue
    {
        $this->assertSameCurrency($other);

        return new PriceValue($this->value + $other->value, $this->currency);
    }

    /**
     * Subtract another PriceValue. Throws if currencies differ.
     * Result may be negative (refund / over-discount scenarios).
     */
    public function subtract(PriceValue $other): PriceValue
    {
        $this->assertSameCurrency($other);

        return new PriceValue($this->value - $other->value, $this->currency);
    }

    /**
     * Multiply this value by an integer scalar (e.g. line quantity).
     * No rounding question — integer * integer is exact.
     */
    public function multiply(int $multiplier): PriceValue
    {
        return new PriceValue($this->value * $multiplier, $this->currency);
    }

    /**
     * Clamp the value to a minimum of zero. Useful after subtractions
     * that may have over-shot (e.g. discount > subtotal edge cases).
     */
    public function clampToZero(): PriceValue
    {
        return $this->value < 0
            ? new PriceValue(0, $this->currency)
            : $this;
    }

    /**
     * Equality on (value, currency).
     */
    public function equals(PriceValue $other): bool
    {
        return $this->currency->getKey() === $other->currency->getKey()
            && $this->value === $other->value;
    }

    public function isZero(): bool
    {
        return $this->value === 0;
    }

    public function isNegative(): bool
    {
        return $this->value < 0;
    }

    /**
     * Sum an iterable of PriceValues. All entries must share `$currency`
     * or {@see MismatchedCurrencyException} is thrown. Returns a zero
     * PriceValue in `$currency` for an empty input — the caller passes
     * the currency explicitly so empty-collection cases can't silently
     * fall through.
     *
     * @param  iterable<PriceValue>  $values
     */
    public static function sum(iterable $values, Currency $currency): PriceValue
    {
        $total = 0;

        foreach ($values as $value) {
            if ($value->currency->getKey() !== $currency->getKey()) {
                throw new MismatchedCurrencyException(
                    "Cannot sum PriceValue in {$value->currency->code} into a {$currency->code} total."
                );
            }
            $total += $value->value;
        }

        return new PriceValue($total, $currency);
    }

    private function assertSameCurrency(PriceValue $other): void
    {
        if ($this->currency->getKey() !== $other->currency->getKey()) {
            throw new MismatchedCurrencyException(
                "Currency mismatch: {$this->currency->code} vs {$other->currency->code}."
            );
        }
    }
}
