<?php

namespace Lunar\Checkout\Contracts;

use Lunar\Core\Models\Cart;

/**
 * A payment method that can say, in the customer's words, why it is not
 * offered for a basket (spec 0002 §B). isAvailable() decides; this explains.
 * A gateway with a minimum charge, say, reports "Card payments need an order
 * total of at least £0.30" so the empty payment region is never a mystery.
 */
interface ExplainsUnavailability
{
    /**
     * Customer-facing reason the method is unavailable for this basket, or
     * null when the method is available (or has nothing useful to say).
     */
    public function unavailableReason(Cart $cart): ?string;
}
