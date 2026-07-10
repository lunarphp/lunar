<?php

namespace Lunar\Core\Actions\Customers;

use Lunar\Core\Contracts\Actions\Customers\CreatesCustomerAddress;
use Lunar\Core\Models\Address;
use Lunar\Core\Models\Customer;

class CreateCustomerAddress implements CreatesCustomerAddress
{
    public function execute(Customer $customer, array $attributes): Address
    {
        /** @var Address */
        return $customer->addresses()->create($attributes);
    }
}
