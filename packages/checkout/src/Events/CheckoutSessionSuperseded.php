<?php

namespace Lunar\Checkout\Events;

use Lunar\Checkout\Models\CheckoutSession;

/**
 * A new session for the same cart cancelled a prior `Open` one (spec 0010 §F.2).
 * `$session` is the superseded (cancelled) session.
 */
class CheckoutSessionSuperseded extends CheckoutSessionEvent
{
    public function __construct(
        CheckoutSession $session,
        public CheckoutSession $supersededBy,
    ) {
        parent::__construct($session);
    }
}
