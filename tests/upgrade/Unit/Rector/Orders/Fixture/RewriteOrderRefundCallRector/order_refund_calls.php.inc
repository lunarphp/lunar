<?php

namespace Lunar\Tests\Upgrade\Unit\Rector\Orders\Fixture\RewriteOrderRefundCallRector;

use Lunar\Core\Models\Order;

function refundThreeArgs(Order $order, $transactionId, $amount, $notes): void
{
    $order->refund($transactionId, $amount, $notes);
}

function refundTwoArgs(Order $order, $transactionId, $amount): void
{
    $order->refund($transactionId, $amount);
}

?>
-----
<?php

namespace Lunar\Tests\Upgrade\Unit\Rector\Orders\Fixture\RewriteOrderRefundCallRector;

use Lunar\Core\Models\Order;

function refundThreeArgs(Order $order, $transactionId, $amount, $notes): void
{
    $order->refund(new \Lunar\Core\DataObjects\RefundRequest(transactionId: $transactionId, adjustment: $amount, notes: $notes));
}

function refundTwoArgs(Order $order, $transactionId, $amount): void
{
    $order->refund(new \Lunar\Core\DataObjects\RefundRequest(transactionId: $transactionId, adjustment: $amount));
}

?>
