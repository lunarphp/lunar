<?php

namespace Lunar\Shipping\Contracts\Actions\ShippingMethods;

use Lunar\Shipping\Models\ShippingMethod;

interface CreatesShippingMethod
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(array $attributes): ShippingMethod;
}
