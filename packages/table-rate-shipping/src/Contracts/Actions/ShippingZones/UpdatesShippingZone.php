<?php

namespace Lunar\Shipping\Contracts\Actions\ShippingZones;

use Lunar\Shipping\Models\ShippingZone;

interface UpdatesShippingZone
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(ShippingZone $shippingZone, array $attributes): ShippingZone;
}
