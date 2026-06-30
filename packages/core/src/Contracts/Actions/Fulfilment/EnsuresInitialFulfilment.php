<?php

namespace Lunar\Core\Contracts\Actions\Fulfilment;

use Lunar\Core\Models\Fulfilment;
use Lunar\Core\Models\Order;

interface EnsuresInitialFulfilment
{
    /**
     * Ensure the order has its initial fulfilment — a single fulfilment covering
     * every physical line at full quantity. Idempotent: a no-op when the order
     * already has any fulfilment, or has no physical lines.
     *
     * Returns the created fulfilment, or null when nothing was created.
     */
    public function execute(Order $order): ?Fulfilment;
}
