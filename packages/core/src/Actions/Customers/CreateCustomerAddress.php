<?php

namespace Lunar\Core\Actions\Customers;

use Lunar\Core\Contracts\Actions\Customers\CreatesCustomerAddress;
use Lunar\Core\Models\Address;
use Lunar\Core\Models\Customer;

class CreateCustomerAddress implements CreatesCustomerAddress
{
    public function execute(Customer $customer, array $attributes): Address
    {
        /** @var Address $address */
        $address = $customer->addresses()->create($attributes);

        // Explicit timeline entry on the customer, so address changes show
        // up in the customer's activity log alongside created/updated.
        activity()
            ->causedBy(auth()->user())
            ->performedOn($customer)
            ->event('address-created')
            ->log('address-created');

        return $address;
    }
}
