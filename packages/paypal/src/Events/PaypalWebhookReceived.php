<?php

namespace Lunar\Paypal\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Dispatched for every verified webhook the driver accepts, handled or not, so
 * consumers can extend coverage without subclassing the job.
 */
class PaypalWebhookReceived
{
    use Dispatchable, SerializesModels;

    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        public string $eventType,
        public array $payload,
    ) {}
}
