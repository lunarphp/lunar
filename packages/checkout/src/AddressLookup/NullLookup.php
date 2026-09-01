<?php

namespace Lunar\Checkout\AddressLookup;

use Lunar\Checkout\Contracts\AddressLookup;

/**
 * The default. A store with no lookup vendor configured gets honest manual
 * address entry rather than a search box that can never answer.
 */
class NullLookup implements AddressLookup
{
    public function lookup(string $postcode): array
    {
        return [];
    }

    public function isAvailable(): bool
    {
        return false;
    }
}
