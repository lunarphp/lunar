<?php

namespace Lunar\Shipping\DataTransferObjects;

use Lunar\Models\Contracts\Cart as CartContract;
use Lunar\Shipping\Models\ShippingRate;

class ShippingOptionRequest
{
    /**
     * Initialise the shipping option request class.
     */
    public function __construct(
        public ShippingRate $shippingRate,
        public CartContract $cart
    ) {
        //
    }
}
