<?php

namespace Lunar\Observers;

use Lunar\Models\Discount;

class DiscountObserver
{
    /**
     * Handle the Discount "deleting" event.
     *
     * @return void
     */
    public function deleting(Discount $discount)
    {
        $discount->brands()->detach();
        $discount->collections()->detach();
        $discount->customerGroups()->detach();
        $discount->purchasables()->detach();
        $discount->users()->detach();
    }
}
