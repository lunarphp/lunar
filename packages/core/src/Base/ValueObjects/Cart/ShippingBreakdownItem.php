<?php

namespace Lunar\Core\Base\ValueObjects\Cart;

use Lunar\Core\DataTypes\Price;

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
