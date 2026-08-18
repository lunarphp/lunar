<?php

namespace Lunar\Core\Contracts\Actions\Customers;

use Lunar\Core\Models\Customer;

interface UpdatesCustomer
{
    /**
     * @param  array<string, mixed>  $attributes
     * @param  ?array<int, int>  $customerGroupIds  null leaves group membership untouched; an empty array clears it
     */
    public function execute(Customer $customer, array $attributes, ?array $customerGroupIds = null): Customer;
}
