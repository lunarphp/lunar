<?php

namespace Lunar\Shipping\Contracts\Actions\ShippingZones;

use Lunar\Shipping\Models\ShippingZone;

interface CreatesShippingZone
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(array $attributes): ShippingZone;
}
