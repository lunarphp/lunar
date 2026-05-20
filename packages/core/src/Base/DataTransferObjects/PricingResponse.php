<?php

namespace Lunar\Core\Base\DataTransferObjects;

use Illuminate\Support\Collection;
use Lunar\Core\Models\Contracts\Price;

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
