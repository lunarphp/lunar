<?php

namespace Lunar\Base\ValueObjects\Cart;

use Lunar\DataTypes\Price;

class ShippingBreakdownItem
{
    public function __construct(
        public string $description,
        public string $code,
        public Price $price
    ) {
        //
    }
}
