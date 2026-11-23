<?php

namespace Lunar\Core\Contracts\Actions\Orders;

use Lunar\Core\DataObjects\PaymentCapture;
use Lunar\Core\Models\Order;

interface CapturesOrder
{
    /**
     * Capture an amount against a successful intent transaction. Guard
     * failures throw `OrderActionException`; a capture the driver declines is
     * returned as a `PaymentCapture` with `success` false and the driver's
     * reason in `message`, so callers must check `success`.
     */
    public function execute(
        Order $order,
        int|string $transactionId,
        float|int|string $amount,
    ): PaymentCapture;
}
