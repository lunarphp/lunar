<?php

namespace Lunar\Core\Base\ValueObjects\Cart;

use Lunar\Core\DataObjects\PriceValue;

class ShippingBreakdownItem
{
    public function __construct(
        public string $name,
        public string $identifier,
        public PriceValue $price
    ) {
        //
    }
}
