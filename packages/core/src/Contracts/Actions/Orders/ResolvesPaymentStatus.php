<?php

namespace Lunar\Core\Contracts\Actions\Orders;

use Lunar\Core\Models\Contracts\Order as OrderContract;
use Lunar\Core\States\Order\Payment\PaymentState;

interface ResolvesPaymentStatus
{
    /**
     * Resolve the derived payment status from the order's transaction ledger.
     *
     * @return class-string<PaymentState>
     */
    public function execute(OrderContract $order): string;
}
