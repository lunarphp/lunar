<?php

namespace Lunar\Core\DataObjects;

use Lunar\Core\Contracts\ReconcilesPaymentIntents;
use Lunar\Core\Enums\PaymentIntentStatus;

/**
 * An intent's status and money as reported by
 * {@see ReconcilesPaymentIntents::fetchIntent()}.
 *
 * `amountMinor` is what the intent is for (the authorised amount, for a hold),
 * `capturedMinor` what has been captured, which for a hold may be less, and
 * `refundedMinor` the refunds that have succeeded or are pending. Refund state
 * lives in the amounts rather than in status cases: a fully refunded intent is
 * Captured with `refundedMinor` equal to `capturedMinor`.
 *
 * @experimental The shape may change in a minor release until a second driver
 *               implements the payment intent capabilities.
 */
class PaymentIntentSnapshot
{
    /**
     * @param  string  $currency  ISO 4217 code.
     */
    public function __construct(
        public readonly PaymentIntentStatus $status,
        public readonly string $currency,
        public readonly int $amountMinor,
        public readonly int $capturedMinor = 0,
        public readonly int $refundedMinor = 0,
    ) {}
}
