<?php

namespace Lunar\Core\Actions\Customers;

use Lunar\Core\Contracts\Actions\Customers\DeletesCustomer;
use Lunar\Core\Models\Customer;

class DeleteCustomer implements DeletesCustomer
{
    public function execute(Customer $customer): void
    {
        $customer->delete();
    }
}
