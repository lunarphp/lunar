<?php

namespace Lunar\Core\Observers;

use Lunar\Core\Models\Discount;

class DiscountObserver
{
    /**
     * Handle the Discount "deleting" event.
     */
    public function deleting(Discount $discount): void
    {
        $discount->brands()->detach();
        $discount->collections()->detach();
        $discount->customerGroups()->detach();
        $discount->customers()->detach();
        $discount->discountables()->delete();
        $discount->users()->detach();
    }
}
