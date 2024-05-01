<?php

namespace Lunar\Shipping\Managers;

use Illuminate\Support\Manager;
use Lunar\Models\Cart;
use Lunar\Shipping\Drivers\ShippingMethods\Collection;
use Lunar\Shipping\Drivers\ShippingMethods\FlatRate;
use Lunar\Shipping\Drivers\ShippingMethods\FreeShipping;
use Lunar\Shipping\Drivers\ShippingMethods\ShipBy;
use Lunar\Shipping\Interfaces\ShippingMethodManagerInterface;
use Lunar\Shipping\Resolvers\ShippingOptionResolver;
use Lunar\Shipping\Resolvers\ShippingRateResolver;
use Lunar\Shipping\Resolvers\ShippingZoneResolver;

class ShippingManager extends Manager implements ShippingMethodManagerInterface
{
    public function createFreeShippingDriver()
    {
        return $this->buildProvider(FreeShipping::class);
    }

    public function createFlatRateDriver()
    {
        return $this->buildProvider(FlatRate::class);
    }

    public function createShipByDriver()
    {
        return $this->buildProvider(ShipBy::class);
    }

    public function createCollectionDriver()
    {
        return $this->buildProvider(Collection::class);
    }

    public function getSupportedDrivers(): \Illuminate\Support\Collection
    {
        return collect([
            'free-shipping' => $this->createDriver('free-shipping'),
            'flat-rate' => $this->createDriver('flat-rate'),
            'ship-by' => $this->createDriver('ship-by'),
            'collection' => $this->createDriver('collection'),
        ])->merge(
            collect($this->customCreators)->mapWithKeys(function ($creator, $key) {
                return [
                    $key => $this->callCustomCreator($key),
                ];
            })
        );
    }

    /**
     * Find the zone for a given address.
     */
    public function zones(): ShippingZoneResolver
    {
        return app(ShippingZoneResolver::class);
    }

    public function shippingRates(Cart $cart = null): ShippingRateResolver
    {
        return new ShippingRateResolver($cart);
    }

    public function shippingOptions(Cart $cart = null): ShippingOptionResolver
    {
        return new ShippingOptionResolver($cart);
    }

    /**
     * Build a shipping provider instance
     *
     * @param  string  $provider
     * @return mixed
     */
    public function buildProvider($provider)
    {
        return $this->container->make($provider);
    }

    public function getDefaultDriver()
    {
        return 'free-shipping';
    }
}
