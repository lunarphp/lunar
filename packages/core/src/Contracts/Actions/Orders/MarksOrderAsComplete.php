<?php

namespace Lunar\Core\Contracts\Actions\Orders;

use Lunar\Core\Models\Contracts\Order as OrderContract;
use Lunar\Core\Models\Order;

interface MarksOrderAsComplete
{
    public function execute(OrderContract $order): Order;
}
