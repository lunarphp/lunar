<?php

namespace Lunar\Checkout\Events;

use Lunar\Checkout\DataObjects\CartSnapshot;
use Lunar\Checkout\Models\CheckoutSession;

/**
 * Amounts re-pinned from the live cart while `Open` (spec 0010 §D).
 */
class CheckoutSessionResynced extends CheckoutSessionEvent
{
    public function __construct(
        CheckoutSession $session,
        public CartSnapshot $snapshot,
    ) {
        parent::__construct($session);
    }
}
