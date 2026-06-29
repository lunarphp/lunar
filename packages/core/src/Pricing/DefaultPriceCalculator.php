<?php

namespace Lunar\Core\Pricing;

use InvalidArgumentException;
use Lunar\Core\Models\Currency;

class DefaultPriceCalculator implements PriceCalculatorInterface
{
    /**
     * Decimal-places threshold at which {@see toMinor()} switches from
     * float math to string math (bcmul). Subclass and override to change
     * the cutover.
     */
    protected int $bcMathThreshold = 4;

    public function percentage(int $value, float $rate, Currency $currency): int
    {
        return (int) round($value * $rate);
    }

    public function withTax(int $value, float $rate, Currency $currency): int
    {
        if ($rate === 0.0) {
            return $value;
        }

        return (int) round($value * (1 + $rate));
    }

    public function withoutTax(int $value, float $rate, Currency $currency): int
    {
        if ($rate === 0.0) {
            return $value;
        }

        $candidate = (int) round($value / (1 + $rate));

        // Adjust so that withTax($candidate) is the largest result <= $value.
        // For values reachable by withTax(), this guarantees the strict
        // round-trip: withTax(withoutTax($v)) === $v.
        while ($this->withTax($candidate, $rate, $currency) > $value) {
            $candidate--;
        }

        while ($this->withTax($candidate + 1, $rate, $currency) <= $value) {
            $candidate++;
        }

        return $candidate;
    }

    public function distribute(int $total, array $weights, Currency $currency): array
    {
        if ($weights === []) {
            return [];
        }

        foreach ($weights as $weight) {
            if ($weight < 0) {
                throw new InvalidArgumentException('Negative weights are not allowed in distribute().');
            }
        }

        $weightTotal = array_sum($weights);

        if ($weightTotal === 0) {
            return array_map(fn () => 0, $weights);
        }

        $allocations = [];
        $remainders = [];

        foreach ($weights as $key => $weight) {
            $exact = $total * $weight / $weightTotal;
            $floor = (int) floor($exact);
            $allocations[$key] = $floor;
            $remainders[$key] = $exact - $floor;
        }

        $remaining = $total - array_sum($allocations);

        if ($remaining === 0) {
            return $allocations;
        }

        $step = $remaining > 0 ? 1 : -1;
        $remaining = abs($remaining);

        arsort($remainders);

        foreach (array_keys($remainders) as $key) {
            if ($remaining === 0) {
                break;
            }

            $allocations[$key] += $step;
            $remaining--;
        }

        return $allocations;
    }

    public function toMinor(int|float|string $major, Currency $currency): int
    {
        if ($currency->decimal_places >= $this->bcMathThreshold) {
            $scaled = bcmul((string) $major, (string) $currency->factor, 1);

            return $this->bcRoundHalfUp($scaled);
        }

        return (int) round((float) $major * $currency->factor);
    }

    public function toMajor(int $minor, Currency $currency): float
    {
        return $minor / $currency->factor;
    }

    /**
     * Round a bc-math string with one fractional digit to an int,
     * half-away-from-zero (matching PHP's `round()` default).
     */
    protected function bcRoundHalfUp(string $value): int
    {
        if (bccomp($value, '0', 1) >= 0) {
            return (int) bcadd($value, '0.5', 0);
        }

        return (int) bcsub($value, '0.5', 0);
    }
}
