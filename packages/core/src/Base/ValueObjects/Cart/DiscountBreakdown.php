<?php

namespace Lunar\Core\Base\ValueObjects\Cart;

use Illuminate\Support\Collection;
use Lunar\Core\DataObjects\PriceValue;
use Lunar\Core\Models\Contracts\Discount;

class DiscountBreakdown
{
    public function __construct(
        public PriceValue $price,
        public Collection $lines,
        public Discount $discount,
    ) {
        //
    }
}
