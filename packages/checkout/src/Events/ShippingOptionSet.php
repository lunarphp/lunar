<?php

namespace Lunar\Checkout\Events;

use Lunar\Checkout\Models\CheckoutSession;

class ShippingOptionSet extends CheckoutSessionEvent
{
    public function __construct(
        CheckoutSession $session,
        public string $optionIdentifier,
    ) {
        parent::__construct($session);
    }
}
