<?php

namespace Lunar\Core\Contracts;

use Lunar\Core\Models\Order;

interface OrderReferenceGenerator
{
    /**
     * Generate a reference for the order.
     */
    public function generate(Order $order): string;
}
