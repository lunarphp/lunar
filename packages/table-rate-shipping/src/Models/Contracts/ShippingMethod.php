<?php

namespace Lunar\Shipping\Models\Contracts;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Lunar\Shipping\Interfaces\ShippingRateInterface;

interface ShippingMethod
{
    public function shippingRates(): HasMany;

    public function driver(): ShippingRateInterface;
}
