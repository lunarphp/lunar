<?php

namespace Lunar\Shipping\Contracts\Actions\ShippingMethods;

use Lunar\Shipping\Models\ShippingMethod;

interface DeletesShippingMethod
{
    public function execute(ShippingMethod $shippingMethod): void;
}
