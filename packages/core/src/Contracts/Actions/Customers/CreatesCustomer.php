<?php

namespace Lunar\Core\Contracts\Actions\Customers;

use Lunar\Core\Models\Customer;

interface CreatesCustomer
{
    /**
     * @param  array<string, mixed>  $attributes
     * @param  array<int, int>  $customerGroupIds
     */
    public function execute(array $attributes, array $customerGroupIds = []): Customer;
}
