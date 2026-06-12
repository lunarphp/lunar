<?php

namespace Lunar\Checkout\Events;

use Lunar\Checkout\Models\CheckoutSession;

class CustomerAssociated extends CheckoutSessionEvent
{
    public function __construct(
        CheckoutSession $session,
        public string $customerReference,
    ) {
        parent::__construct($session);
    }
}
