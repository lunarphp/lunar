<?php

namespace Lunar\Tests\Core\Stubs;

use Lunar\Core\Base\OrderReferenceGeneratorInterface;
use Lunar\Core\Models\Contracts\Order;

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
