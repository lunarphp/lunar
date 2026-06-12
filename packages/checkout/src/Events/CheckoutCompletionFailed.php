<?php

namespace Lunar\Checkout\Events;

use Lunar\Checkout\Models\CheckoutSession;

/**
 * The §E.2 completion re-verify or order creation failed, or a misdirected
 * gateway success event was refunded (spec 0010 §E.2). `$reason` is a stable
 * machine code distinguishing `refunded` / `refund-pending` / `refund-failed`
 * (plus `verify-mismatch` when nothing was captured yet).
 */
class CheckoutCompletionFailed extends CheckoutSessionEvent
{
    public function __construct(
        CheckoutSession $session,
        public string $reason,
        public ?string $refundReference = null,
    ) {
        parent::__construct($session);
    }
}
