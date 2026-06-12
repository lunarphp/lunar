<?php

namespace Lunar\Checkout\Support;

use Lunar\Checkout\Models\CheckoutSession;
use Lunar\Core\Contracts\SupportsPaymentIntents;
use Lunar\Core\Managers\PaymentManager;

/**
 * Resolves the gateway driver behind a session's in-flight intent, when that
 * driver opted into the {@see SupportsPaymentIntents} capability. The payment
 * method handle is recorded on `meta.payment_method` when the intent is
 * created. Null means the intent's outcome cannot be confirmed — callers treat
 * that as "unabortable" and leave the session for reconciliation (0010 §F).
 */
class PaymentIntentGateway
{
    public function __construct(
        private PaymentManager $payments,
    ) {}

    public function for(CheckoutSession $session): ?SupportsPaymentIntents
    {
        $handle = $session->meta['payment_method'] ?? null;

        if (! is_string($handle) || $handle === '') {
            return null;
        }

        try {
            $driver = $this->payments->driver($handle);
        } catch (\InvalidArgumentException) {
            return null;
        }

        return $driver instanceof SupportsPaymentIntents ? $driver : null;
    }
}
