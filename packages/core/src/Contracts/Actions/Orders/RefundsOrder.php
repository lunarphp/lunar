<?php

namespace Lunar\Core\Contracts\Actions\Orders;

use Lunar\Core\DataObjects\PaymentRefund;
use Lunar\Core\Models\Contracts\Order as OrderContract;

interface RefundsOrder
{
    public function execute(
        OrderContract $order,
        int|string $transactionId,
        float|int|string $amount,
        ?string $notes = null,
    ): PaymentRefund;
}
