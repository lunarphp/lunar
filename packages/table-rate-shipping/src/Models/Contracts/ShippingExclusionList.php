<?php

namespace Lunar\Shipping\Models\Contracts;

use Illuminate\Database\Eloquent\Relations\HasMany;

interface ShippingExclusionList
{
    /**
     * Return the shipping zone relationship.
     */
    public function exclusions(): HasMany;
}
