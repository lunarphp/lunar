<?php

namespace Lunar\Core\Contracts\Actions\Discounts;

use Lunar\Core\Models\Coupon;
use Lunar\Core\Models\Discount;

interface CreatesCoupon
{
    /**
     * @param  array{code: string, max_uses?: ?int, email?: ?string}  $attributes
     */
    public function execute(Discount $discount, array $attributes): Coupon;
}
