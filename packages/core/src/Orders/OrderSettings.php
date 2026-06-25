<?php

namespace Lunar\Core\Orders;

use Lunar\Core\Contracts\OrderSettings as OrderSettingsContract;
use Lunar\Core\Models\Contracts\Order;

/**
 * The batteries-included order settings. Auto-close is off, so closing a
 * settled order stays a deliberate action unless a consumer opts in by binding
 * their own implementation. The store-scoped end state will resolve the
 * preference per order; this default ignores it.
 */
class OrderSettings implements OrderSettingsContract
{
    public function autoClosesSettledOrders(Order $order): bool
    {
        return false;
    }
}
