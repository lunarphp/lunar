<?php

namespace Lunar\Shipping\Models\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Lunar\Core\DataTypes\ShippingOption;
use Lunar\Core\Models\Cart;

interface ShippingRate
{
    public function shippingZone(): BelongsTo;

    public function shippingMethod(): BelongsTo;

    /**
     * Return the shipping method driver.
     */
    public function getShippingOption(Cart $cart): ?ShippingOption;
}
