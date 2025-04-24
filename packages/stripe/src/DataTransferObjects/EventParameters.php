<?php

namespace Lunar\Stripe\DataTransferObjects;

class EventParameters
{
    public function __construct(
        public string $paymentIntentId,
        public ?int $orderId = null,
    ) {}
}
