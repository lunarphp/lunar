<?php

namespace Lunar\Core\Contracts\Actions\Orders;

use Lunar\Core\DataObjects\PaymentCapture;
use Lunar\Core\Models\Order;

interface CapturesOrder
{
    public function execute(
        Order $order,
        int|string $transactionId,
        float|int|string $amount,
    ): PaymentCapture;
}
