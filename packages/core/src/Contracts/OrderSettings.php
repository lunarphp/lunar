<?php

namespace Lunar\Core\Contracts;

use Lunar\Core\Models\Order;

/**
 * Store-level order preferences. Code-level defaults today; the store-scoped
 * (Channel) end state resolves them per order. Bind a custom implementation
 * from a service provider to override — the seam that keeps these preferences
 * out of process-global config.
 */
interface OrderSettings
{
    /**
     * Whether an order should be automatically closed (archived) the moment it
     * becomes fully paid and fulfilled, instead of waiting for a manual close.
     */
    public function autoClosesSettledOrders(Order $order): bool;
}
