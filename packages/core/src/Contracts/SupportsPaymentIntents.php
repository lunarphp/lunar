<?php

namespace Lunar\Core\Contracts;

use Lunar\Core\Enums\PaymentIntentStatus;

/**
 * Opt-in capability for payment drivers that expose an intent lifecycle.
 *
 * Checked via `instanceof` (like the checkout element capabilities) — never a
 * required part of {@see PaymentType}. The checkout reconciliation layer uses
 * it to void in-flight intents, look up an intent's outcome, and refund a
 * captured intent before any order/transaction exists. A driver without the
 * capability cannot be voided or reconciled: its checkout sessions stay
 * `PaymentProcessing` and flow into the stall protocol.
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
     * Refund a captured intent by reference, before any order/transaction
     * exists. `$idempotencyKey` is derived from the intent reference so sweep
     * retries never double-refund. Returns the gateway's refund reference.
     */
    public function refundIntent(string $reference, int $amountMinor, string $idempotencyKey): string;
}
