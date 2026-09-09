<?php

namespace Lunar\Shipping\Contracts\Actions\ShippingMethods;

use Lunar\Shipping\Models\ShippingMethod;

interface UpdatesShippingMethod
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(ShippingMethod $shippingMethod, array $attributes): ShippingMethod;
}
