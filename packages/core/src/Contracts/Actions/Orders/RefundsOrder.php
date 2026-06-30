<?php

namespace Lunar\Core\Contracts\Actions\Orders;

use Lunar\Core\DataObjects\PaymentRefund;
use Lunar\Core\Models\Order;

interface RefundsOrder
{
    public function execute(
        Order $order,
        int|string $transactionId,
        float|int|string $amount,
        ?string $notes = null,
    ): PaymentRefund;
}
