<?php

namespace Lunar\Core\Contracts\Actions\Orders;

use Lunar\Core\Models\Contracts\Order as OrderContract;
use Lunar\Core\States\Order\Fulfilment\FulfilmentStatus;

interface ResolvesFulfilmentStatus
{
    /**
     * Resolve the derived order-level fulfilment status from the quantities
     * the order's fulfilments cover against its physical lines.
     *
     * @return class-string<FulfilmentStatus>
     */
    public function execute(OrderContract $order): string;
}
