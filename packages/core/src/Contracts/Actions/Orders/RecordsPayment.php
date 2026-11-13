<?php

namespace Lunar\Core\Contracts\Actions\Orders;

use Lunar\Core\Models\Order;
use Lunar\Core\Models\Transaction;

interface RecordsPayment
{
    /**
     * Record a payment taken outside a gateway, in minor units.
     */
    public function execute(Order $order, int $amount, ?string $reference = null, ?string $notes = null): Transaction;
}
