<?php

namespace Lunar\Core\Contracts;

use Lunar\Core\DataObjects\HoldDescription;
use Lunar\Core\DataObjects\PaymentIntentDescriptor;
use Lunar\Core\Enums\HoldAdjustment;
use Lunar\Core\Models\Cart;

/**
 * Opt-in capability for payment drivers that can authorise a hold now and
 * capture it later: the wallet sheet authorises, a later confirmation
 * captures. Checked via `instanceof`, like {@see CreatesPaymentIntents} —
 * never a required part of {@see PaymentType}.
 *
 * Releasing a hold is {@see SupportsPaymentIntents::voidIntent()}, which is
 * why this extends that contract rather than adding a release verb of its
 * own. No gateway can authorise a hold without also being able to void it, and
 * the extension means a caller that has checked for this capability can void
 * without a second check — the money-back path is the last place that should
 * fail on a missing method.
 */
interface SupportsPaymentHolds extends SupportsPaymentIntents
{
    /**
     * Create (or resume) the cart's authorise-only intent: capture deferred,
     * amount adjustment requested where the rail offers it. Idempotent per
     * cart, like {@see CreatesPaymentIntents::createIntent()}.
     */
    public function createHold(Cart $cart): PaymentIntentDescriptor;

    /**
     * Describe a hold by reference: live status, authorised amount, and the
     * wallet that authorised it (null when the reference is unknown or is
     * not a hold). Never trusts a client claim; always asks the gateway.
     */
    public function describeHold(string $reference): ?HoldDescription;

    /**
     * Bring an authorised hold to a new payable total. Ok when the hold now
     * covers it (already did, or was incremented); NeedsReauthorization when
     * this hold cannot stretch and the customer must authorise again.
     */
    public function adjustHold(string $reference, int $amountMinor): HoldAdjustment;

    /**
     * Capture an authorised hold for the final amount (at most the authorised
     * figure). Idempotent per reference. MUST throw when the gateway cannot
     * confirm the capture; an unknown outcome is not a capture.
     */
    public function captureHold(string $reference, int $amountMinor): void;
}
