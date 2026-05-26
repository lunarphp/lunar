<?php

namespace Lunar\Core\Facades;

use Illuminate\Support\Facades\Facade;
use Lunar\Core\Contracts\TelemetryService;

/**
 * @method static void optOut()
 * @method static string getInsightsUrl()
 * @method static string getCacheKey()
 * @method static bool shouldRun()
 * @method static void run()
 *
 * @see \Lunar\Core\Telemetry\TelemetryService
 */
class Telemetry extends Facade
{
    public static function getFacadeAccessor(): string
    {
        return TelemetryService::class;
    }
}
