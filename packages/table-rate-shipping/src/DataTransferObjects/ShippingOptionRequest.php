<?php

namespace Lunar\Shipping\DataTransferObjects;

use Lunar\Models\Cart;
use Lunar\Shipping\Models\ShippingMethod;

class ShippingOptionRequest
{
    /**
     * Initialise the shipping option request class.
     */
    public function __construct(
        public ShippingMethod $shippingMethod,
        public Cart $cart
    ) {
        //
    }
}
