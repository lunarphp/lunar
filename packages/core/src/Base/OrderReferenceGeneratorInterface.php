<?php

namespace Lunar\Base;

use Lunar\Models\Order;

interface OrderReferenceGeneratorInterface
{
    /**
     * Generate a reference for the order.
     *
     * @param  \Lunar\Models\Order  $order
     * @return string
     */
    public function generate(Order $order): string;
}
