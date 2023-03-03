<?php

namespace Lunar\Base\ValueObjects\Cart;

use Illuminate\Support\Collection;

class DiscountBreakdown
{
    public function __construct(
        public ?Collection $discounts = null
    ) {
        $this->discounts = $discounts ?: collect();
    }

    /**
     * Add a discount breakdown.
     *
     * @param  DiscountBreakdownValue  $discountBreakdownValue
     * @return void
     */
    public function addDiscount(DiscountBreakdownValue $discountBreakdownValue)
    {
        $this->discounts->push($discountBreakdownValue);
    }
}
