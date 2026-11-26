<?php

namespace Lunar\Core\Actions\Discounts;

use Lunar\Core\Contracts\Actions\Discounts\DeletesCoupon;
use Lunar\Core\Facades\DB;
use Lunar\Core\Models\Coupon;

/**
 * Delete a code. Its redemptions stay, without the coupon, and orders keep the
 * code in their discount breakdown.
 */
class DeleteCoupon implements DeletesCoupon
{
    public function execute(Coupon $coupon): void
    {
        DB::transaction(function () use ($coupon): void {
            // The foreign key nulls these too, but not on a connection that leaves foreign keys off.
            $coupon->redemptions()->update(['coupon_id' => null]);

            $coupon->delete();
        });
    }
}
