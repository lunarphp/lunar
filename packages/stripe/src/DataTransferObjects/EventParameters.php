<?php

namespace Lunar\Stripe\DataTransferObjects;

use Lunar\Models\Order;
use Stripe\PaymentIntent;

class EventParameters
{
    public function __construct(
        public string $paymentIntentId,
        public ?int $orderId = null,
    ) {}
}
