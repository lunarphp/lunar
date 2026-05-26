<?php

namespace Lunar\Core\Facades;

use Illuminate\Support\Facades\Facade;
use Lunar\Core\Pricing\PriceCalculatorInterface;

/**
 * @method static int percentage(int $value, float $rate, \Lunar\Core\Models\Contracts\Currency $currency)
 * @method static int withTax(int $value, float $rate, \Lunar\Core\Models\Contracts\Currency $currency)
 * @method static int withoutTax(int $value, float $rate, \Lunar\Core\Models\Contracts\Currency $currency)
 * @method static array<int|string, int> distribute(int $total, array $weights, \Lunar\Core\Models\Contracts\Currency $currency)
 * @method static int toMinor(int|float|string $major, \Lunar\Core\Models\Contracts\Currency $currency)
 * @method static float toMajor(int $minor, \Lunar\Core\Models\Contracts\Currency $currency)
 *
 * @see PriceCalculatorInterface
 */
class PriceCalculator extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return PriceCalculatorInterface::class;
    }
}
