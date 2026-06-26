<?php

namespace Lunar\Core\Contracts\Actions\Orders;

use Lunar\Core\Models\Contracts\Order as OrderContract;
use Lunar\Core\Models\Order;

interface RecomputesOrderStatus
{
    /**
     * Recompute the order's derived `payment_status` and `fulfilment_status`,
     * then re-derive the headline `status` — unless the order is in a manual
     * override state, which suppresses derivation. Pass `$notify: false` to
     * suppress the customer notification a fulfilment-status change triggers.
     */
    public function execute(OrderContract $order, bool $notify = true): Order;
}
