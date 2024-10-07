<?php

namespace Lunar\Base\ValueObjects\Cart;

use Illuminate\Support\Collection;
use Lunar\DataTypes\Price;
use Lunar\Models\Contracts\Discount;

class DiscountBreakdown
{
    public function __construct(
        public Price $price,
        public Collection $lines,
        public Discount $discount,
    ) {
        //
    }
}
