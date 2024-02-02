<?php

namespace Lunar\Base\DataTransferObjects;

use Illuminate\Support\Collection;
use Lunar\Models\Price;

class PricingResponse
{
    public function __construct(
        public Price $matched,
        public Price $base,
        public Collection $quantityBreaks,
        public Collection $customerGroupPrices,
    ) {
        //
    }
}
