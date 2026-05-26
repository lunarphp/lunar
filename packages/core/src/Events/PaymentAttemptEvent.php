<?php

namespace Lunar\Core\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Lunar\Core\DataObjects\PaymentAuthorize;

class PaymentAttemptEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public PaymentAuthorize $paymentAuthorize
    ) {}
}
