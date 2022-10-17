<?php

namespace Lunar\ValueObjects\Cart;

use Lunar\DataTypes\Price;

final class TaxBreakdownAmount
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
