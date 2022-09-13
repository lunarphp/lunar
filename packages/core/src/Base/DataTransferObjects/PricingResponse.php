<?php

namespace Lunar\Base\DataTransferObjects;

use Lunar\Models\Price;
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
