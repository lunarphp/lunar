<?php

namespace Lunar\Core\Actions\Customers;

use Lunar\Core\Contracts\Actions\Customers\DeletesCustomerAddress;
use Lunar\Core\Models\Address;

class DeleteCustomerAddress implements DeletesCustomerAddress
{
    public function execute(Address $address): void
    {
        $address->delete();
    }
}
