<?php

namespace Lunar\Checkout\Managers;

use Illuminate\Support\Manager;
use Lunar\Checkout\AddressLookup\IdealPostcodes;
use Lunar\Checkout\AddressLookup\NullLookup;
use Lunar\Checkout\Contracts\AddressLookup;

/**
 * Resolves the active address-lookup driver by name from
 * `config('lunar.checkout.address_lookup.driver')`, mirroring
 * CheckoutSessionManager. Hosts add their own with `extend()`.
 */
class AddressLookupManager extends Manager
{
    public function getDefaultDriver(): string
    {
        return $this->config->get('lunar.checkout.address_lookup.driver', 'null');
    }

    public function createNullDriver(): AddressLookup
    {
        return new NullLookup;
    }

    public function createIdealPostcodesDriver(): AddressLookup
    {
        return $this->container->make(IdealPostcodes::class);
    }
}
