<?php

namespace Lunar\Core\Contracts\Actions\Customers;

use Lunar\Core\Models\Address;

interface DeletesCustomerAddress
{
    public function execute(Address $address): void;
}
