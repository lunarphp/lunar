<?php

namespace Lunar\Core\Observers;

use Lunar\Core\Events\Customers\CustomerCreated;
use Lunar\Core\Events\Customers\CustomerDeleted;
use Lunar\Core\Events\Customers\CustomerUpdated;
use Lunar\Core\Models\Customer;

class CustomerObserver
{
    public function created(Customer $customer): void
    {
        CustomerCreated::dispatch($customer);
    }

    public function updated(Customer $customer): void
    {
        CustomerUpdated::dispatch($customer);
    }

    public function deleted(Customer $customer): void
    {
        CustomerDeleted::dispatch($customer);
    }

    /**
     * Handle the Customer "deleting" event.
     *
     * Releases foreign key references so the customer can be deleted without
     * constraint violations. Order and cart rows are kept (customer_id nulled);
     * addresses are owned by the customer and removed.
     */
    public function deleting(Customer $customer): void
    {
        $customer->carts()->update(['customer_id' => null]);
        $customer->orders()->update(['customer_id' => null]);
        $customer->addresses()->delete();

        $customer->customerGroups()->detach();
        $customer->discounts()->detach();
        $customer->users()->detach();
    }
}
