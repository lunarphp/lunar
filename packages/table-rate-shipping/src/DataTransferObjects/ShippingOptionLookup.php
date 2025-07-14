<?php

namespace Lunar\Shipping\DataTransferObjects;

use InvalidArgumentException;
use Illuminate\Support\Collection;
use Lunar\Shipping\Models\ShippingRate;

class ShippingOptionLookup
{
    /**
     * Initialise the shipping option lookup class.
     */
    public function __construct(
        public Collection $shippingRates
    ) {
        throw_if(
            $this->shippingRates->filter(
                fn ($method) => get_class($method) != ShippingRate::class
            )->count(),
            InvalidArgumentException::class
        );
    }
}
