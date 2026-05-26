<?php

namespace Lunar\Core\Contracts\Actions\Orders;

use Lunar\Core\Models\Contracts\Order as OrderContract;
use Lunar\Core\Models\Order;

interface MarksOrderAsShipped
{
    public function execute(OrderContract $order): Order;
}
