<?php

namespace Lunar\Checkout\Events;

use Lunar\Checkout\Models\CheckoutSession;

class CollectionPointSet extends CheckoutSessionEvent
{
    public function __construct(
        CheckoutSession $session,
        public string $handle,
    ) {
        parent::__construct($session);
    }
}
