<?php

namespace Lunar\Core\Contracts;

use Lunar\Core\DataObjects\PaymentRefund;
use Lunar\Core\Enums\PaymentIntentStatus;

/**
 * Opt-in capability for payment drivers that expose an intent lifecycle.
 *
 * Checked via `instanceof`, like {@see CreatesPaymentIntents} — never a
 * required part of {@see PaymentType}. It is the reconciliation surface for
 * the phase before an order exists: void an in-flight intent the customer
 * abandoned, look up an intent's outcome, and refund one that was captured
 * against an order that was never placed.
 *
 * A driver without the capability cannot be reconciled. A caller must treat
 * its in-flight payments as unresolved rather than assume they were
 * abandoned, because nothing can tell it which happened.
 */
interface SupportsPaymentIntents
{
    /**
     * Report the gateway's current status for the given intent reference.
     */
    public function fetchIntent(string $reference): PaymentIntentStatus;

    /**
     * Abort an in-flight (uncaptured) intent. MUST throw if the gateway cannot
     * confirm the void — an unknown outcome is not a void.
     */
    public function voidIntent(string $reference): void;

    /**
     * Refund a captured intent by reference, before any order or transaction
     * exists. `$idempotencyKey` is derived from the intent reference so sweep
     * retries never double-refund.
     *
     * The returned {@see PaymentRefund} always carries a null `transaction`:
     * `transactions.order_id` is not nullable, and the premise of this call is
     * that no order was placed. Its `reference` carries the gateway's own
     * refund reference for audit.
     */
    public function refundIntent(string $reference, int $amountMinor, string $idempotencyKey): PaymentRefund;
}
