<?php

namespace Lunar\Shipping\Contracts\Actions\ShippingRates;

use Lunar\Shipping\Models\ShippingRate;

interface DeletesShippingRate
{
    public function execute(ShippingRate $shippingRate): void;
}
