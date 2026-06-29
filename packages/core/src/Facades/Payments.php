<?php

namespace Lunar\Core\Facades;

use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Facades\Facade;
use Lunar\Core\Contracts\PaymentManager;

/**
 * @method static void createOfflineDriver()
 * @method static mixed buildProvider(string $provider)
 * @method static void getDefaultDriver()
 * @method static mixed driver(string|null $driver = null)
 * @method static \Lunar\Core\Managers\PaymentManager extend(string $driver, \Closure $callback)
 * @method static array getDrivers()
 * @method static Container getContainer()
 * @method static \Lunar\Core\Managers\PaymentManager setContainer(Container $container)
 * @method static \Lunar\Core\Managers\PaymentManager forgetDrivers()
 *
 * @see PaymentManager
 */
class Payments extends Facade
{
    public static function getFacadeAccessor()
    {
        return PaymentManager::class;
    }
}
