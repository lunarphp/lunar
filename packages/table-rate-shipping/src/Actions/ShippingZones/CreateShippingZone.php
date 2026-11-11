<?php

namespace Lunar\Shipping\Actions\ShippingZones;

use Lunar\Shipping\Contracts\Actions\ShippingZones\CreatesShippingZone;
use Lunar\Shipping\Models\ShippingZone;

/**
 * Create a shipping zone from its own columns. Coverage is set afterwards
 * through UpdateShippingZone.
 */
class CreateShippingZone implements CreatesShippingZone
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(array $attributes): ShippingZone
    {
        return ShippingZone::create($attributes);
    }
}
