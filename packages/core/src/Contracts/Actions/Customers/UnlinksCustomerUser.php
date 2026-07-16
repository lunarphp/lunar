<?php

namespace Lunar\Core\Contracts\Actions\Customers;

use Lunar\Core\Models\Customer;

interface UnlinksCustomerUser
{
    public function execute(Customer $customer, int|string $userId): void;
}
