<?php

namespace Lunar\Shipping\Contracts\Actions\ShippingExclusionLists;

use Lunar\Shipping\Models\ShippingExclusionList;

interface CreatesShippingExclusionList
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(array $attributes): ShippingExclusionList;
}
