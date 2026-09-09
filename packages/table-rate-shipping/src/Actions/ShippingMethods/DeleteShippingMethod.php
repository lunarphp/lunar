<?php

namespace Lunar\Shipping\Actions\ShippingMethods;

use Lunar\Shipping\Contracts\Actions\ShippingMethods\DeletesShippingMethod;
use Lunar\Shipping\Models\ShippingMethod;

/**
 * Delete a shipping method. The model's deleting hook detaches its customer
 * groups and removes its rates.
 */
class DeleteShippingMethod implements DeletesShippingMethod
{
    public function execute(ShippingMethod $shippingMethod): void
    {
        $shippingMethod->delete();
    }
}
