<?php

namespace Lunar\Core\Contracts;

use Lunar\Core\DataObjects\PaymentRefund;
use Lunar\Core\Enums\PaymentIntentStatus;
use Lunar\Core\Exceptions\PaymentIntentException;

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
 *
 * Every method here throws {@see PaymentIntentException} rather than reporting
 * failure by return. Each one answers a question about money that has already
 * moved or is about to, so a clean return is something a caller acts on: it
 * settles a payment, releases a hold, or tells a customer a refund is coming.
 * A driver that cannot establish the outcome MUST throw and let the caller
 * ask again, never return a value that reads as settled.
 */
interface SupportsPaymentIntents
{
    /**
     * Report the gateway's current status for the given intent reference.
     *
     * The return is never "don't know": a reference the gateway does not
     * recognise throws, exactly like an unreachable gateway does. Guessing a
     * status for an unknown reference would have a caller settle or abandon a
     * payment on no evidence, and there is no safe direction to guess in.
     *
     * @throws PaymentIntentException when the gateway cannot report a status,
     *                                including an unrecognised reference
     */
    public function fetchIntent(string $reference): PaymentIntentStatus;

    /**
     * Abort an in-flight (uncaptured) intent. An unknown outcome is not a
     * void: a caller that saw this return cleanly is entitled to treat the
     * money as released.
     *
     * @throws PaymentIntentException when the gateway cannot confirm the void
     */
    public function voidIntent(string $reference): void;

    /**
     * Refund a captured intent by reference, before any order or transaction
     * exists. `$idempotencyKey` is derived from the intent reference so sweep
     * retries never double-refund.
     *
     * Returning describes a refund that happened, and only that. A refund the
     * gateway cannot confirm throws; it MUST NOT come back as a
     * {@see PaymentRefund} with `success` false. There is one failure channel
     * here, as on the rest of this contract, because a caller that sees this
     * return is entitled to tell the customer their money is on its way and to
     * stop chasing it.
     *
     * The returned object therefore always carries `success` true and a null
     * `transaction`: `transactions.order_id` is not nullable, and the premise
     * of this call is that no order was placed. Its `reference` carries the
     * gateway's own refund reference for audit, and `message` any detail the
     * gateway returned alongside it.
     *
     * @throws PaymentIntentException when the gateway cannot confirm the refund
     */
    public function refundIntent(string $reference, int $amountMinor, string $idempotencyKey): PaymentRefund;
}
