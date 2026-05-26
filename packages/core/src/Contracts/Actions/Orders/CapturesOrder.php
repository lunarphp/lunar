<?php

namespace Lunar\Core\Contracts\Actions\Orders;

use Lunar\Core\DataObjects\PaymentCapture;
use Lunar\Core\Models\Contracts\Order as OrderContract;

interface CapturesOrder
{
    public function execute(
        OrderContract $order,
        int|string $transactionId,
        float|int|string $amount,
    ): PaymentCapture;
}
