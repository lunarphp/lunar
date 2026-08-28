<?php

namespace Lunar\Core\Actions\Discounts;

use Lunar\Core\Contracts\Actions\Discounts\DeletesDiscount;
use Lunar\Core\Facades\DB;
use Lunar\Core\Models\Discount;

/**
 * Delete a discount.
 *
 * Tearing down the targeting and availability pivots is DiscountObserver's
 * job, so that every delete path gets it. The transaction is this action's
 * addition: the observer's detaches and the delete itself are separate
 * statements, and a discount left behind with its targeting already stripped
 * would silently apply to the whole catalogue.
 */
class DeleteDiscount implements DeletesDiscount
{
    public function execute(Discount $discount): void
    {
        DB::transaction(function () use ($discount): void {
            $discount->delete();
        });
    }
}
