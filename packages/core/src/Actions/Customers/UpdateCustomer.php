<?php

namespace Lunar\Core\Actions\Customers;

use Lunar\Core\Contracts\Actions\Customers\UpdatesCustomer;
use Lunar\Core\Models\Customer;

/**
 * Update a customer's attributes and, when a group set is given, sync it to
 * those customer groups — an empty set clears any current groups, while null
 * leaves group membership untouched.
 */
class UpdateCustomer implements UpdatesCustomer
{
    public function execute(Customer $customer, array $attributes, ?array $customerGroupIds = null): Customer
    {
        $customer->update($attributes);

        if ($customerGroupIds !== null) {
            $customer->customerGroups()->sync($customerGroupIds);
        }

        return $customer;
    }
}
