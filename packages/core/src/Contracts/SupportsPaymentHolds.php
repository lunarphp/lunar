<?php

namespace Lunar\Core\Contracts;

use Lunar\Core\DataObjects\HoldDescription;
use Lunar\Core\DataObjects\PaymentIntentDescriptor;
use Lunar\Core\Enums\HoldAdjustment;
use Lunar\Core\Exceptions\PaymentIntentException;
use Lunar\Core\Models\Cart;

/**
 * Opt-in capability for payment drivers that can authorise a hold now and
 * capture it later: the wallet sheet authorises, a later confirmation
 * captures. Checked via `instanceof`, never a required part of
 * {@see PaymentType}.
 *
 * Releasing a hold is {@see ReconcilesPaymentIntents::voidIntent()}, which is
 * why this extends that contract rather than adding a release verb of its
 * own. A caller that has checked for this capability can void without a
 * second check, so the money-back path never fails on a missing method.
 *
 * A hold is its own flavour, separate from the cart's standard intent: a cart
 * can have one live of each. When the customer switches from one to the
 * other, the caller voids the one it is leaving.
 *
 * @experimental The shape may change in a minor release until a second driver
 *               implements these capabilities.
 */
interface SupportsPaymentHolds extends ReconcilesPaymentIntents
{
    /**
     * Create (or resume) the cart's authorise-only intent. Idempotent per cart
     * and per flavour, with the same key and rotation rules as
     * {@see CreatesPaymentIntents::createIntent()}.
     *
     * Never returns a hold in a currency other than the cart's. An unconfirmed
     * hold is re-priced where the gateway allows it; an authorised one is
     * voided and replaced, and if that void fails this throws rather than open
     * a second authorisation.
     *
     * A short hold is still live, so this returns it unchanged. To authorise
     * again after {@see adjustHold()} reports NeedsReauthorization, the caller
     * voids the stored reference first and only then calls this; if the void
     * throws, it does not call this.
     *
     * @throws PaymentIntentException when the gateway refuses the call, cannot
     *                                be reached, or returns something the
     *                                driver cannot interpret, or when a hold
     *                                in the wrong currency cannot be voided
     */
    public function createHold(Cart $cart): PaymentIntentDescriptor;

    /**
     * Describe a hold by reference: live status, authorised amount, and the
     * wallet that authorised it. Never trusts a client claim; always asks the
     * gateway, or the driver record a webhook-driven gateway keeps.
     *
     * The hold must belong to the cart. A driver MUST return null when the
     * reference is unknown, is not a hold, was not created by
     * {@see createHold()} for this cart, or is in a different currency from
     * the cart. The reference comes from the client, so without this check a
     * caller could be shown someone else's hold, or one whose larger
     * minor-unit amount is in a cheaper currency. A non-null return is in the
     * cart's currency and belongs to it.
     *
     * A null means do not proceed. For a reference that came from the client,
     * never pass it to {@see ReconcilesPaymentIntents::voidIntent()},
     * {@see adjustHold()} or {@see captureHold()}: they take a bare reference
     * with no ownership check, so doing so would let a client act on someone
     * else's hold. For a reference the application stored from its own
     * {@see createHold()} call, a null means the hold no longer fits the cart
     * (usually a currency change): void it, then call {@see createHold()}.
     *
     * Returning null here where {@see ReconcilesPaymentIntents::fetchIntent()}
     * throws is deliberate: this reference is an unverified client claim, and
     * that one is a reference the application stored itself.
     *
     * @throws PaymentIntentException when the gateway cannot be asked
     */
    public function describeHold(Cart $cart, string $reference): ?HoldDescription;

    /**
     * Bring an authorised hold to a new payable total. Ok only when coverage
     * is confirmed (it already covered it, or the gateway confirmed the
     * increment), because the caller captures straight after.
     * NeedsReauthorization when the hold cannot be confirmed to stretch,
     * including a gateway that adjusts asynchronously and has not confirmed.
     *
     * Only pass a reference {@see describeHold()} has accepted for the cart,
     * or one the application stored from {@see createHold()}.
     *
     * @throws PaymentIntentException when the gateway cannot confirm either
     *                                outcome, which is not the same as
     *                                NeedsReauthorization
     */
    public function adjustHold(string $reference, int $amountMinor): HoldAdjustment;

    /**
     * Capture an authorised hold for the final amount, at most the authorised
     * figure: an amount above it throws and is never clamped, so a customer is
     * never undercharged while the order is handed over.
     *
     * Idempotent per reference and amount: a repeat at the amount already
     * captured or requested returns cleanly, and a repeat at a different
     * amount throws. A driver whose gateway confirms captures asynchronously
     * records the requested amount before calling the gateway, so the check
     * holds while the capture is pending. A clean return means the gateway
     * accepted the capture, so the caller may hand the customer their order.
     *
     * Only pass a reference {@see describeHold()} has accepted for the cart,
     * or one the application stored from {@see createHold()}.
     *
     * @throws PaymentIntentException when the amount exceeds the authorisation,
     *                                differs from an earlier capture of this
     *                                reference, or the gateway refuses or
     *                                cannot confirm the capture
     */
    public function captureHold(string $reference, int $amountMinor): void;
}
