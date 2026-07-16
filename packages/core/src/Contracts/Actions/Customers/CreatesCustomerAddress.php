<?php

namespace Lunar\Core\Contracts\Actions\Customers;

use Lunar\Core\Models\Address;
use Lunar\Core\Models\Customer;

interface CreatesCustomerAddress
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(Customer $customer, array $attributes): Address;
}
