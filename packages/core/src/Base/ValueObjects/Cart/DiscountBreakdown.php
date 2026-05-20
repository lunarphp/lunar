<?php

namespace Lunar\Core\Base\ValueObjects\Cart;

use Illuminate\Support\Collection;
use Lunar\Core\DataTypes\Price;
use Lunar\Core\Models\Contracts\Discount;

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
