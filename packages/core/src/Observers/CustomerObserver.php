<?php

namespace Lunar\Observers;

use Lunar\Models\Customer;

class CustomerObserver
{
    /**
     * Handle the Discount "deleting" event.
     *
     * @return void
     */
    public function deleting(Customer $customer)
    {
        $customer->customerGroups()->detach();
        $customer->discounts()->detach();
        $customer->users()->detach();
    }
}
