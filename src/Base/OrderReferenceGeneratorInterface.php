<?php

namespace GetCandy\Base;

use Closure;
use GetCandy\Models\Order;
use Illuminate\Support\Collection;

interface OrderReferenceGeneratorInterface
{
    /**
     * Generate a reference for the order.
     *
     * @param  \GetCandy\Models\Order  $order
     * @return string
     */
    public function generate(Order $order): string;
}
