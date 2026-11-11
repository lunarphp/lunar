<?php

namespace Lunar\Shipping\Contracts\Actions\ShippingExclusionLists;

use Lunar\Shipping\Models\ShippingExclusionList;

interface UpdatesShippingExclusionList
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(ShippingExclusionList $shippingExclusionList, array $attributes): ShippingExclusionList;
}
