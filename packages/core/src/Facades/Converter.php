<?php

namespace Lunar\Facades;

use Illuminate\Support\Facades\Facade;
use Lunar\Utils\MeasurementConverter;

/**
 * @method static MeasurementConverter from(string $measurement)
 * @method static MeasurementConverter to(string $measurement)
 * @method static MeasurementConverter value(float $value)
 * @method static MeasurementConverter convert()
 * @method static float getValue()
 * @method static string format(?string $formatString = null)
 * @method static array getMeasurements()
 * @method static void setMeasurements(array $measurements)
 *
 * @see MeasurementConverter
 */
class Converter extends Facade
{
    protected static function getFacadeAccessor()
    {
        return MeasurementConverter::class;
    }
}
