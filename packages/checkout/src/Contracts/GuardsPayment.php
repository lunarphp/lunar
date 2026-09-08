<?php

namespace Lunar\Checkout\Contracts;

use Lunar\Checkout\Models\CheckoutSession;
use Lunar\Core\Models\Cart;

/**
 * A payment method that can refuse to proceed at the pay boundary for a
 * reason the customer can fix (spec 0014 §C): a purchase order reference the
 * account requires, say. Availability (isAvailable) says whether the method
 * is offered at all; the blocker says whether it may be used right now.
 */
interface GuardsPayment
{
    /**
     * A customer-facing reason pay must not proceed with this method, or
     * null when it may.
     */
    public function paymentBlocker(CheckoutSession $session, Cart $cart): ?string;
}
