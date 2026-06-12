<?php

namespace Lunar\Checkout\Events;

use Lunar\Checkout\Models\CheckoutSession;

/**
 * Session transitioned to `Cancelled` (spec 0010 §F). `$reason` is a stable
 * machine code.
 */
class CheckoutSessionInvalidated extends CheckoutSessionEvent
{
    public function __construct(
        CheckoutSession $session,
        public string $reason,
    ) {
        parent::__construct($session);
    }
}
