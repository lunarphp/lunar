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
    /**
     * `?:` rather than a config default: `env('CHECKOUT_ADDRESS_LOOKUP_DRIVER', 'null')`
     * resolves the literal string "null" to PHP null (Env::getOption maps it),
     * so the stored config value is a real null and Config::get never reaches
     * its own default. A null or empty value means "no lookup", which is the
     * null driver, not a TypeError on the way to one.
     */
    public function getDefaultDriver(): string
    {
        return $this->config->get('lunar.checkout.address_lookup.driver') ?: 'null';
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
