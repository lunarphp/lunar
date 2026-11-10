<?php

namespace Lunar\Observers;

use Lunar\Models\Contracts\Customer as CustomerContract;

class CustomerObserver
{
    /**
     * Handle the Customer "deleting" event.
     *
     * Releases foreign key references so the customer can be deleted without
     * constraint violations. Order and cart rows are kept (customer_id nulled);
     * addresses are owned by the customer and removed.
     */
    public function deleting(CustomerContract $customer): void
    {
        $customer->carts()->update(['customer_id' => null]);
        $customer->orders()->update(['customer_id' => null]);
        $customer->addresses()->delete();

        $customer->customerGroups()->detach();
        $customer->discounts()->detach();
        $customer->users()->detach();
    }
}
