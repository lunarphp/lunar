<?php

namespace GetCandy\Base\DataTransferObjects;

use GetCandy\DataTypes\Price;
use Illuminate\Support\Collection;

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
