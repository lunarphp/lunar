<?php

namespace Lunar\Core\Contracts\Actions\Orders;

use Lunar\Core\DataObjects\PaymentRefund;
use Lunar\Core\DataObjects\RefundRequest;
use Lunar\Core\Models\Order;

interface RefundsOrder
{
    /**
     * Refund against a successful capture transaction. Guard failures throw
     * `OrderActionException`; a refund the driver declines is returned as a
     * `PaymentRefund` with `success` false and the driver's reason in
     * `message`, so callers must check `success`.
     */
    public function execute(Order $order, RefundRequest $request): PaymentRefund;
}
