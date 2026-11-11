<?php

namespace Lunar\Shipping\Actions\ShippingRates;

use Lunar\Shipping\Contracts\Actions\ShippingRates\DeletesShippingRate;
use Lunar\Shipping\Models\ShippingRate;

/**
 * Delete a shipping rate. The model's deleting hook removes its prices.
 */
class DeleteShippingRate implements DeletesShippingRate
{
    public function execute(ShippingRate $shippingRate): void
    {
        $shippingRate->delete();
    }
}
