<?php

namespace Lunar\Core\Contracts\Actions\Customers;

use Lunar\Core\Models\Customer;

interface LinksCustomerUser
{
    public function execute(Customer $customer, string $email): void;
}
