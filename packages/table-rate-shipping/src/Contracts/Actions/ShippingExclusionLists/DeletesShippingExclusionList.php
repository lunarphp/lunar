<?php

namespace Lunar\Shipping\Contracts\Actions\ShippingExclusionLists;

use Lunar\Shipping\Models\ShippingExclusionList;

interface DeletesShippingExclusionList
{
    public function execute(ShippingExclusionList $shippingExclusionList): void;
}
