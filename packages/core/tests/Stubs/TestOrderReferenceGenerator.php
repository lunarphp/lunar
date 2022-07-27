<?php

namespace GetCandy\Tests\Stubs;

use GetCandy\Base\OrderReferenceGeneratorInterface;
use GetCandy\Models\Order;

class TestOrderReferenceGenerator implements OrderReferenceGeneratorInterface
{
    /**
     * Called just after cart totals are calculated.
     *
     * @return void
     */
    public function generate(Order $order): string
    {
        return 'reference-' . $order->id;
    }
}
