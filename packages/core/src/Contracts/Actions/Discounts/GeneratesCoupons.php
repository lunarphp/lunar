<?php

namespace Lunar\Core\Contracts\Actions\Discounts;

use Lunar\Core\DataObjects\CouponBatch;
use Lunar\Core\Models\Discount;

interface GeneratesCoupons
{
    /**
     * Create the batch's codes on the discount and return the batch ULID.
     */
    public function execute(Discount $discount, CouponBatch $batch): string;
}
