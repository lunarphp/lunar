<?php

namespace GetCandy\Base;

use GetCandy\Models\Order;

interface OrderReferenceGeneratorInterface
{
    /**
     * Generate a reference for the order.
     *
     * @param \GetCandy\Models\Order $order
     *
     * @return string
     */
    public function generate(Order $order): string;
}
