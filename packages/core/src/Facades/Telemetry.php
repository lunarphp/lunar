<?php

namespace Lunar\Core\Facades;

use Illuminate\Support\Facades\Facade;
use Lunar\Core\Base\TelemetryService;
use Lunar\Core\Base\TelemetryServiceInterface;

/**
 * @method static void optOut()
 * @method static string getInsightsUrl()
 * @method static string getCacheKey()
 * @method static bool shouldRun()
 * @method static void run()
 *
 * @see TelemetryService
 */
class Telemetry extends Facade
{
    public static function getFacadeAccessor(): string
    {
        return TelemetryServiceInterface::class;
    }
}
