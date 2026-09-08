<?php

namespace Lunar\Shipping\Actions\ShippingExclusionLists;

use Lunar\Shipping\Contracts\Actions\ShippingExclusionLists\CreatesShippingExclusionList;
use Lunar\Shipping\Models\ShippingExclusionList;

class CreateShippingExclusionList implements CreatesShippingExclusionList
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(array $attributes): ShippingExclusionList
    {
        return ShippingExclusionList::create($attributes);
    }
}
