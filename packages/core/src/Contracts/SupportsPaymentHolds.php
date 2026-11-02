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
     * and per flavour, with the same persisted-key rule as
     * {@see CreatesPaymentIntents::createIntent()}.
     *
     * @throws PaymentIntentException when the gateway refuses the call, cannot
     *                                be reached, or returns something the
     *                                driver cannot interpret
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
     * Returning null here where {@see ReconcilesPaymentIntents::fetchIntent()}
     * throws is deliberate: this reference is an unverified client claim, and
     * that one is a reference the application stored itself.
     *
     * @throws PaymentIntentException when the gateway cannot be asked
     */
    public function describeHold(Cart $cart, string $reference): ?HoldDescription;

    /**
     * Bring an authorised hold to a new payable total. Ok when the hold now
     * covers it (already did, or was incremented); NeedsReauthorization when
     * this hold cannot stretch and the customer must authorise again.
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
     * Capture an authorised hold for the final amount (at most the authorised
     * figure). Idempotent per reference. A clean return means the gateway
     * accepted the capture, so the caller may hand the customer their order.
     *
     * Only pass a reference {@see describeHold()} has accepted for the cart,
     * or one the application stored from {@see createHold()}.
     *
     * @throws PaymentIntentException when the gateway refuses or cannot confirm
     *                                the capture
     */
    public function captureHold(string $reference, int $amountMinor): void;
}
