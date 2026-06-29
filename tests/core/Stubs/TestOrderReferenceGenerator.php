<?php

namespace Lunar\Tests\Core\Stubs;

use Lunar\Core\Contracts\OrderReferenceGenerator;
use Lunar\Core\Models\Order;

class TestOrderReferenceGenerator implements OrderReferenceGenerator
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
