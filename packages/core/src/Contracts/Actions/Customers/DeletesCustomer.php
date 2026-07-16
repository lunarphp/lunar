<?php

namespace Lunar\Core\Contracts\Actions\Customers;

use Lunar\Core\Models\Customer;

interface DeletesCustomer
{
    public function execute(Customer $customer): void;
}
