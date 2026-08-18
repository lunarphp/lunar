<?php

namespace Lunar\Core\Actions\Customers;

use Lunar\Core\Contracts\Actions\Customers\CreatesCustomer;
use Lunar\Core\Models\Customer;

/**
 * Create a customer and, when given, sync it to the requested customer groups.
 */
class CreateCustomer implements CreatesCustomer
{
    public function execute(array $attributes, array $customerGroupIds = []): Customer
    {
        $customer = Customer::create($attributes);

        if ($customerGroupIds !== []) {
            $customer->customerGroups()->sync($customerGroupIds);
        }

        return $customer;
    }
}
