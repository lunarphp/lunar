<?php

namespace Lunar\Core\Facades;

use Illuminate\Support\Facades\Facade;
use Lunar\Core\Contracts\PaymentManager;

/**
 * @method static void createOfflineDriver()
 * @method static mixed buildProvider(string $provider)
 * @method static void getDefaultDriver()
 * @method static mixed driver(string|null $driver = null)
 * @method static \Lunar\Core\Managers\PaymentManager extend(string $driver, \Closure $callback)
 * @method static array getDrivers()
 * @method static \Illuminate\Contracts\Container\Container getContainer()
 * @method static \Lunar\Core\Managers\PaymentManager setContainer(\Illuminate\Contracts\Container\Container $container)
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
