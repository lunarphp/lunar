<?php

namespace Lunar\Tests\Stubs;

use Lunar\Base\OrderReferenceGeneratorInterface;
use Lunar\Models\Order;

class TestOrderReferenceGenerator implements OrderReferenceGeneratorInterface
{
    /**
     * Called just after cart totals are calculated.
     *
     * @return void
     */
    public function generate(Order $order): string
    {
        return 'reference-'.$order->id;
    }
}
