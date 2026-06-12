<?php

namespace Lunar\Checkout\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Lunar\Checkout\Models\CheckoutSession;

/**
 * Base for the granular checkout-session events (spec 0010 §G). Each carries
 * the session plus the relevant payload.
 */
abstract class CheckoutSessionEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public CheckoutSession $session,
    ) {}
}
