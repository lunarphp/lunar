<?php

namespace Lunar\Shipping\Facades;

use Illuminate\Support\Facades\Facade;
use Lunar\Shipping\Interfaces\ShippingMethodManagerInterface;

/**
 * @method static void createFreeShippingDriver()
 * @method static void createFlatRateDriver()
 * @method static void createShipByDriver()
 * @method static void createCollectionDriver()
 * @method static \Illuminate\Support\Collection getSupportedDrivers()
 * @method static \Lunar\Shipping\Resolvers\ShippingZoneResolver zones()
 * @method static \Lunar\Shipping\Resolvers\ShippingRateResolver shippingRates(\Lunar\Models\Contracts\Cart|null $cart = null)
 * @method static \Lunar\Shipping\Resolvers\ShippingOptionResolver shippingOptions(\Lunar\Models\Contracts\Cart|null $cart = null)
 * @method static mixed buildProvider(string $provider)
 * @method static void getDefaultDriver()
 * @method static mixed driver(string|null $driver = null)
 * @method static \Lunar\Shipping\Managers\ShippingManager extend(string $driver, \Closure $callback)
 * @method static array getDrivers()
 * @method static \Illuminate\Contracts\Container\Container getContainer()
 * @method static \Lunar\Shipping\Managers\ShippingManager setContainer(\Illuminate\Contracts\Container\Container $container)
 * @method static \Lunar\Shipping\Managers\ShippingManager forgetDrivers()
 *
 * @see \Lunar\Shipping\Managers\ShippingManager
 */
class Shipping extends Facade
{
    public static function getFacadeAccessor(): string
    {
        return ShippingMethodManagerInterface::class;
    }
}
