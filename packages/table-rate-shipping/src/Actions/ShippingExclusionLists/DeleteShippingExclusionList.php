<?php

namespace Lunar\Shipping\Actions\ShippingExclusionLists;

use Lunar\Shipping\Contracts\Actions\ShippingExclusionLists\DeletesShippingExclusionList;
use Lunar\Shipping\Models\ShippingExclusionList;

/**
 * Delete an exclusion list. The model's deleting hook removes its exclusions
 * and detaches it from every zone.
 */
class DeleteShippingExclusionList implements DeletesShippingExclusionList
{
    public function execute(ShippingExclusionList $shippingExclusionList): void
    {
        $shippingExclusionList->delete();
    }
}
