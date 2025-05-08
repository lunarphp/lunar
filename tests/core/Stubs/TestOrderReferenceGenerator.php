<?php

namespace Lunar\Tests\Core\Stubs;

use Lunar\Base\OrderReferenceGeneratorInterface;
use Lunar\Models\Contracts\Order;

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
