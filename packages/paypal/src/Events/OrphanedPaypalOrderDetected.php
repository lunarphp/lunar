<?php

namespace Lunar\Paypal\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * PayPal took the customer's payment but the Lunar order could not be created
 * for the cart, for example because stock ran out. Nothing is refunded
 * automatically; listen for this to refund, back-order or contact the customer.
 */
class OrphanedPaypalOrderDetected
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public string $paypalOrderId,
        public ?int $cartId,
        public ?string $reason = null,
    ) {}
}
