<?php

namespace Lunar\Checkout\Events;

use Lunar\Checkout\Models\CheckoutSession;

class FulfilmentSet extends CheckoutSessionEvent
{
    public function __construct(
        CheckoutSession $session,
        public string $mode,
    ) {
        parent::__construct($session);
    }
}
