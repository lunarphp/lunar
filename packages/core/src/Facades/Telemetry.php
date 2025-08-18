<?php

namespace Lunar\Facades;

use Illuminate\Support\Facades\Facade;
use Lunar\Base\TelemetryServiceInterface;

/**
 * @method static void optOut()
 * @method static string getInsightsUrl()
 * @method static string getCacheKey()
 * @method static bool shouldRun()
 * @method static void run()
 *
 * @see \Lunar\Base\TelemetryService
 */
class Telemetry extends Facade
{
    public static function getFacadeAccessor(): string
    {
        return TelemetryServiceInterface::class;
    }
}
