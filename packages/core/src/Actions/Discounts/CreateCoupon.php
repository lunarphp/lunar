<?php

namespace Lunar\Core\Actions\Discounts;

use Lunar\Core\Contracts\Actions\Discounts\CreatesCoupon;
use Lunar\Core\Models\Coupon;
use Lunar\Core\Models\Discount;

/**
 * Add one code to a discount.
 */
class CreateCoupon implements CreatesCoupon
{
    public function execute(Discount $discount, array $attributes): Coupon
    {
        /** @var Coupon */
        return $discount->coupons()->create($attributes);
    }
}
