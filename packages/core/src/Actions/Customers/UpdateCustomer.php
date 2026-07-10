<?php

namespace Lunar\Core\Actions\Customers;

use Lunar\Core\Contracts\Actions\Customers\UpdatesCustomer;
use Lunar\Core\Models\Customer;

/**
 * Update a customer's attributes and sync it to the given customer groups —
 * an empty set clears any groups the customer currently belongs to.
 */
class UpdateCustomer implements UpdatesCustomer
{
    public function execute(Customer $customer, array $attributes, array $customerGroupIds = []): Customer
    {
        $customer->update($attributes);

        $customer->customerGroups()->sync($customerGroupIds);

        return $customer;
    }
}
