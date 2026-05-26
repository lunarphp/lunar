<?php

namespace Lunar\Core\ValueObjects\Cart;

use Lunar\Core\DataObjects\PriceValue;

class TaxBreakdownAmount
{
    public function __construct(
        public PriceValue $price,
        public string $identifier,
        public string $description,
        public float $percentage,
    ) {
        //
    }
}
