<?php

namespace Lunar\Core\Contracts;

use Lunar\Core\DataObjects\PaymentIntentSnapshot;
use Lunar\Core\DataObjects\PaymentRefund;
use Lunar\Core\Enums\PaymentIntentStatus;
use Lunar\Core\Exceptions\PaymentIntentException;

/**
 * Opt-in capability for payment drivers that can report, void and refund an
 * intent by reference, for either flavour (a standard intent or a hold).
 * Checked via `instanceof`, never a required part of {@see PaymentType}. It is
 * the reconciliation surface for the phase before an order exists.
 *
 * A reference is an opaque, driver-owned handle for one intent's whole
 * lifecycle. Callers store it and pass it back; they never parse it. A
 * gateway that issues several ids across the lifecycle is mapped by the
 * driver, from its own record.
 *
 * A driver without the capability cannot be reconciled. A caller must treat
 * its in-flight payments as unresolved rather than assume they were
 * abandoned, because nothing can tell it which happened.
 *
 * Every method throws {@see PaymentIntentException} rather than reporting
 * failure by return. A clean return means the gateway accepted the operation
 * and the driver knows of no failure. It does not always mean the money has
 * settled: some gateways acknowledge a void or refund and report the result
 * later. A failure that arrives after a clean return shows up in
 * {@see fetchIntent()}, so a caller that needs certainty re-fetches.
 *
 * @experimental The shape may change in a minor release until a second driver
 *               implements these capabilities.
 */
interface ReconcilesPaymentIntents
{
    /**
     * Report the intent's status and its money: what it is for, what has been
     * captured and what has been refunded.
     *
     * The return is never "don't know": a reference the gateway does not
     * recognise throws, exactly like an unreachable gateway does. The
     * reference is one the application stored itself, so the gateway having
     * lost it is evidence something is wrong, not an answer.
     *
     * @throws PaymentIntentException when the gateway cannot report the intent,
     *                                including an unrecognised reference
     */
    public function fetchIntent(string $reference): PaymentIntentSnapshot;

    /**
     * Abort an intent that has not been captured. A clean return means
     * nothing is held and the intent is dead to the application: the driver
     * marks it voided, and {@see fetchIntent()} reports
     * {@see PaymentIntentStatus::Voided} from then on. Safe to retry: an
     * intent that is already Voided or {@see PaymentIntentStatus::Failed}
     * returns cleanly.
     *
     * An object the gateway cannot cancel before payment holds no money, so
     * the driver marks it voided locally. That mark never hides the gateway's
     * later answer: if money moves on it anyway, {@see fetchIntent()} reports
     * that status instead, and the caller reconciles it as money taken against
     * no order.
     *
     * @throws PaymentIntentException when the gateway cannot confirm the void,
     *                                and when the intent is
     *                                {@see PaymentIntentStatus::Processing}
     *                                (retry once it settles) or
     *                                {@see PaymentIntentStatus::Captured}
     *                                (refund instead)
     */
    public function voidIntent(string $reference): void;

    /**
     * Refund a captured intent by reference, before any order or transaction
     * exists. Never more than the snapshot's captured amount less what has
     * been refunded; size it from {@see fetchIntent()}, not from the cart.
     *
     * `$idempotencyKey` identifies the refund attempt, not the intent. The
     * caller generates a refund id, persists it before the first attempt and
     * reuses it on every retry; a separate refund of the same intent gets an
     * id of its own. Never derive the key from the arguments: two partial
     * refunds of equal value would share it and one would be dropped. The key
     * only protects while the gateway retains it, so a retry of an old attempt
     * re-fetches and compares `refundedMinor` first.
     *
     * The returned object always carries `success` true and a null
     * `transaction`: a refund the gateway refuses throws instead, and
     * `transactions.order_id` is not nullable. `pending` is true when the
     * gateway accepted the refund but has not settled it, and `reference`
     * carries the gateway's own refund reference for audit.
     *
     * @throws PaymentIntentException when the gateway refuses or cannot confirm
     *                                the refund
     */
    public function refundIntent(string $reference, int $amountMinor, string $idempotencyKey): PaymentRefund;
}
