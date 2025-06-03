<?php

namespace Lunar\Facades;

use Illuminate\Support\Facades\Facade;
use Lunar\Base\TaxManagerInterface;

/**
 * @method static void createSystemDriver()
 * @method static mixed buildProvider(string $provider)
 * @method static void getDefaultDriver()
 * @method static mixed driver(string|null $driver = null)
 * @method static \Lunar\Managers\TaxManager extend(string $driver, \Closure $callback)
 * @method static array getDrivers()
 * @method static \Illuminate\Contracts\Container\Container getContainer()
 * @method static \Lunar\Managers\TaxManager setContainer(\Illuminate\Contracts\Container\Container $container)
 * @method static \Lunar\Managers\TaxManager forgetDrivers()
 *
 * @see \Lunar\Managers\TaxManager
 */
class Taxes extends Facade
{
    public static function getFacadeAccessor()
    {
        return TaxManagerInterface::class;
    }
}
