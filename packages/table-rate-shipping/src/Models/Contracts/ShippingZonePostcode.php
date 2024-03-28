<?php

namespace Lunar\Shipping\Models\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

interface ShippingZonePostcode
{
    /**
     * Return the shipping zone relationship.
     */
    public function shippingZone(): BelongsTo;
}
