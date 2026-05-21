<?php

namespace Lunar\Core\Base;

use Lunar\Core\Models\Contracts\Order;

interface OrderReferenceGeneratorInterface
{
    /**
     * Generate a reference for the order.
     */
    public function generate(Order $order): string;
}
