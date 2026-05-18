<?php

namespace Lunar\Stripe\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrphanedPaymentIntentDetected
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public string $paymentIntentId,
        public ?int $cartId,
        public ?string $reason = null,
    ) {
        //
    }
}
