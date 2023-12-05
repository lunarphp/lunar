<?php

namespace Lunar\Shipping\DataTransferObjects;

use Doctrine\Common\Cache\Psr6\InvalidArgument;
use Illuminate\Support\Collection;
use Lunar\Models\Country;
use Lunar\Shipping\Models\ShippingMethod;

class ShippingOptionLookup
{
    /**
     * Initialise the postcode lookup class.
     *
     * @param Country Country description
     * @param public string description
     */
    public function __construct(
        public Collection $shippingMethods
    ) {
        throw_if(
            $shippingMethods->filter(
                fn ($method) => get_class($method) != ShippingMethod::class
            )->count(),
            new InvalidArgument()
        );
    }
}
