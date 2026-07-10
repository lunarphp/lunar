<?php

namespace Lunar\Core\Actions\Customers;

use Lunar\Core\Contracts\Actions\Customers\UpdatesCustomerAddress;
use Lunar\Core\Models\Address;

class UpdateCustomerAddress implements UpdatesCustomerAddress
{
    public function execute(Address $address, array $attributes): Address
    {
        $address->update($attributes);

        return $address;
    }
}
