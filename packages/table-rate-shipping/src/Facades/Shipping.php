<?php

namespace Lunar\Shipping\Facades;

use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use Lunar\Core\Models\Cart;
use Lunar\Shipping\Interfaces\ShippingMethodManagerInterface;
use Lunar\Shipping\Managers\ShippingManager;
use Lunar\Shipping\Resolvers\ShippingOptionResolver;
use Lunar\Shipping\Resolvers\ShippingRateResolver;
use Lunar\Shipping\Resolvers\ShippingZoneResolver;

/**
 * @method static void createFreeShippingDriver()
 * @method static void createFlatRateDriver()
 * @method static void createShipByDriver()
 * @method static void createCollectionDriver()
 * @method static Collection getSupportedDrivers()
 * @method static ShippingZoneResolver zones()
 * @method static ShippingRateResolver shippingRates(Cart|null $cart = null)
 * @method static ShippingOptionResolver shippingOptions(Cart|null $cart = null)
 * @method static mixed buildProvider(string $provider)
 * @method static void getDefaultDriver()
 * @method static mixed driver(string|null $driver = null)
 * @method static ShippingManager extend(string $driver, \Closure $callback)
 * @method static array getDrivers()
 * @method static Container getContainer()
 * @method static ShippingManager setContainer(Container $container)
 * @method static ShippingManager forgetDrivers()
 *
 * @see ShippingManager
 */
class Shipping extends Facade
{
    public static function getFacadeAccessor(): string
    {
        return ShippingMethodManagerInterface::class;
    }
}
