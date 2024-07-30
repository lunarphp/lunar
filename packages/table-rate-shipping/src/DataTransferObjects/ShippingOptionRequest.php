<?php

namespace Lunar\Shipping\DataTransferObjects;

use Lunar\Models\Cart;
use Lunar\Shipping\Models\ShippingRate;

class ShippingOptionRequest
{
    /**
     * Initialise the shipping option request class.
     */
    public function __construct(
        public ShippingRate $shippingRate,
        public Cart $cart
    ) {
        //
    }
}
