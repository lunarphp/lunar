<?php

namespace Lunar\Checkout\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Lunar\Checkout\Models\CheckoutSession;
use Lunar\Core\Models\Order;

/**
 * The order has resolved and is about to be placed (spec 0001 §I as amended by
 * spec 0011 §F).
 *
 * This is where a host projects what its elements captured onto the order. It
 * fires inside complete()'s transaction and cart-row lock, so a listener's
 * writes are atomic with placement and happen exactly once per order.
 *
 * The package deliberately does not project element data itself: what a
 * captured value means to a merchant's downstream systems is theirs to decide.
 */
class OrderPlacing
{
    use Dispatchable;

    public function __construct(
        public Order $order,
        public CheckoutSession $session,
    ) {}
}
