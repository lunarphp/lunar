<?php

namespace Lunar\Core\Contracts\Actions\Orders;

use Lunar\Core\Models\Order;

interface GeneratesOrderReference
{
    public function execute(Order $order): ?string;
}
