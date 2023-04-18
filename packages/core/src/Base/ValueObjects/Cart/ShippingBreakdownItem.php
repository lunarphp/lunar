<?php

namespace Lunar\Base\ValueObjects\Cart;

use Lunar\DataTypes\Price;

class ShippingBreakdownItem
{
    public function __construct(
        public string $name,
        public string $identifier,
        public Price $price
    ) {
        //
    }
}
