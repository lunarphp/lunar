<?php

namespace Lunar\Core\Contracts\Actions\Discounts;

use Lunar\Core\Models\Coupon;

interface DeletesCoupon
{
    public function execute(Coupon $coupon): void;
}
