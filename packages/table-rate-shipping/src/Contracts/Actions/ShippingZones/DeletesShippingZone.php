<?php

namespace Lunar\Shipping\Contracts\Actions\ShippingZones;

use Lunar\Shipping\Models\ShippingZone;

interface DeletesShippingZone
{
    public function execute(ShippingZone $shippingZone): void;
}
