<?php

namespace Lunar\Core\Facades;

use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Facades\Facade;
use Lunar\Core\Contracts\TaxManager;

/**
 * @method static void createSystemDriver()
 * @method static mixed buildProvider(string $provider)
 * @method static void getDefaultDriver()
 * @method static mixed driver(string|null $driver = null)
 * @method static \Lunar\Core\Managers\TaxManager extend(string $driver, \Closure $callback)
 * @method static array getDrivers()
 * @method static Container getContainer()
 * @method static \Lunar\Core\Managers\TaxManager setContainer(Container $container)
 * @method static \Lunar\Core\Managers\TaxManager forgetDrivers()
 *
 * @see TaxManager
 */
class Taxes extends Facade
{
    public static function getFacadeAccessor()
    {
        return TaxManager::class;
    }
}
