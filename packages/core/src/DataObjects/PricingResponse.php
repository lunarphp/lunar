<?php

namespace Lunar\Core\DataObjects;

use Illuminate\Support\Collection;
use Lunar\Core\Models\Price;

class PricingResponse
{
    public function __construct(
        public Price $matched,
        public Price $base,
        public Collection $priceBreaks,
        public Collection $customerGroupPrices,
    ) {
        //
    }
}
