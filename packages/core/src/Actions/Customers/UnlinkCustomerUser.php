<?php

namespace Lunar\Core\Actions\Customers;

use Lunar\Core\Contracts\Actions\Customers\UnlinksCustomerUser;
use Lunar\Core\Models\Customer;

class UnlinkCustomerUser implements UnlinksCustomerUser
{
    public function execute(Customer $customer, int|string $userId): void
    {
        $customer->users()->detach($userId);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($customer)
            ->event('user-unlinked')
            ->log('user-unlinked');
    }
}
