<?php

namespace Lunar\Base\ValueObjects\Cart;

use Lunar\DataTypes\Price;

class TaxBreakdownAmount
{
    public function __construct(
        public Price $price,
        public string $identifier,
        public string $description,
        public float $percentage,
    ) {
        //
    }
}
