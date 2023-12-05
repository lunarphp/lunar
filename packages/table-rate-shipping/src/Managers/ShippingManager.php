<?php

namespace Lunar\Shipping\Managers;

use Illuminate\Support\Manager;
use Lunar\Models\Cart;
use Lunar\Shipping\Drivers\ShippingMethods\Collection;
use Lunar\Shipping\Drivers\ShippingMethods\FlatRate;
use Lunar\Shipping\Drivers\ShippingMethods\FreeShipping;
use Lunar\Shipping\Drivers\ShippingMethods\ShipBy;
use Lunar\Shipping\Interfaces\ShippingMethodManagerInterface;
use Lunar\Shipping\Resolvers\ShippingMethodResolver;
use Lunar\Shipping\Resolvers\ShippingOptionResolver;
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

    public function getSupportedDrivers()
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
     *
     * @param  Cart  $cart
     * @return Collection
     */
    public function zones()
    {
        return new ShippingZoneResolver();
    }

    public function shippingMethods(Cart $cart = null)
    {
        return new ShippingMethodResolver($cart);
    }

    public function shippingOptions(Cart $cart = null)
    {
        return new ShippingOptionResolver($cart);
    }

    /**
     * Build a tax provider instance.
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
