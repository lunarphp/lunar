<?php

namespace Lunar\Base;

use Lunar\Models\Contracts\Order;

interface OrderReferenceGeneratorInterface
{
    /**
     * Generate a reference for the order.
     */
    public function generate(Order $order): string;
}
