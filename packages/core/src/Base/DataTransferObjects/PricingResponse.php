<?php

namespace GetCandy\Base\DataTransferObjects;

use GetCandy\Models\Price;
use Illuminate\Support\Collection;

class PricingResponse
{
    public function __construct(
        public Price $matched,
        public Price $base,
        public Collection $tiered,
        public Collection $customerGroupPrices,
    ) {
        //
    }
}
