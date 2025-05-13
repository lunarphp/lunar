<?php

namespace Lunar\Observers;

use Lunar\Models\Contracts\Customer as CustomerContract;

class CustomerObserver
{
    /**
     * Handle the Discount "deleting" event.
     *
     * @return void
     */
    public function deleting(CustomerContract $customer)
    {
        $customer->customerGroups()->detach();
        $customer->discounts()->detach();
        $customer->users()->detach();
    }
}
