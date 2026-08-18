<?php

namespace Lunar\Core\Contracts\Actions\Customers;

use Lunar\Core\Models\Address;

interface UpdatesCustomerAddress
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(Address $address, array $attributes): Address;
}
