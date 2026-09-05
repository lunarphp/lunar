<?php

namespace Lunar\Tests\Upgrade\Unit\Rector\Orders\Fixture\RewriteOrderRefundCallRector;

use Lunar\Core\DataObjects\RefundRequest;
use Lunar\Core\Models\Order;

class NotAnOrder
{
    public function refund($a, $b, $c) {}
}

function untouched(NotAnOrder $thing, $a, $b, $c, Order $order, RefundRequest $request): void
{
    $thing->refund($a, $b, $c);
    $order->refund($request);
}
