<?php

namespace Lunar\Facades;

use Illuminate\Support\Facades\Facade;
use Lunar\Base\PaymentManagerInterface;

/**
 * @method static void createOfflineDriver()
 * @method static mixed buildProvider(string $provider)
 * @method static void getDefaultDriver()
 * @method static mixed driver(string|null $driver = null)
 * @method static \Lunar\Managers\PaymentManager extend(string $driver, \Closure $callback)
 * @method static array getDrivers()
 * @method static \Illuminate\Contracts\Container\Container getContainer()
 * @method static \Lunar\Managers\PaymentManager setContainer(\Illuminate\Contracts\Container\Container $container)
 * @method static \Lunar\Managers\PaymentManager forgetDrivers()
 *
 * @see \Lunar\Managers\PaymentManager
 */
class Payments extends Facade
{
    public static function getFacadeAccessor()
    {
        return PaymentManagerInterface::class;
    }
}
