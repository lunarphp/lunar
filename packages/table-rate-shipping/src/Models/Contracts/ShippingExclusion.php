<?php

namespace Lunar\Shipping\Models\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

interface ShippingExclusion
{
    /**
     * Return the shipping zone relationship.
     */
    public function list(): BelongsTo;

    /**
     * Return the purchasable relationship.
     */
    public function purchasable(): MorphTo;
}
