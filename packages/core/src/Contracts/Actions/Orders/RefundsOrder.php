<?php

namespace Lunar\Core\Contracts\Actions\Orders;

use Lunar\Core\DataObjects\PaymentRefund;
use Lunar\Core\DataObjects\RefundRequest;
use Lunar\Core\Models\Order;

interface RefundsOrder
{
    public function execute(Order $order, RefundRequest $request): PaymentRefund;
}
