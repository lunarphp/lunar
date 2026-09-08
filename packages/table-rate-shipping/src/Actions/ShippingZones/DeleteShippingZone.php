<?php

namespace Lunar\Shipping\Actions\ShippingZones;

use Lunar\Shipping\Contracts\Actions\ShippingZones\DeletesShippingZone;
use Lunar\Shipping\Models\ShippingZone;

/**
 * Delete a shipping zone. The model's deleting hook removes its rates,
 * coverage and exclusion-list attachments with it.
 */
class DeleteShippingZone implements DeletesShippingZone
{
    public function execute(ShippingZone $shippingZone): void
    {
        $shippingZone->delete();
    }
}
