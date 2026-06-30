<?php

namespace Lunar\Core\Events\Customers;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Lunar\Core\Models\Customer;

class CustomerCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Customer $customer,
    ) {}
}
