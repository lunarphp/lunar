<?php

namespace Lunar\Checkout\Events;

use Lunar\Checkout\Models\CheckoutSession;

class CouponApplied extends CheckoutSessionEvent
{
    public function __construct(
        CheckoutSession $session,
        public string $couponCode,
    ) {
        parent::__construct($session);
    }
}
