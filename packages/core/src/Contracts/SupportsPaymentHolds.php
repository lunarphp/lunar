<?php

namespace Lunar\Core\Contracts;

use Lunar\Core\DataObjects\HoldDescription;
use Lunar\Core\DataObjects\PaymentIntentDescriptor;
use Lunar\Core\Enums\HoldAdjustment;
use Lunar\Core\Models\Cart;

/**
 * Opt-in capability for payment drivers that can authorise a hold now and
 * capture it later (express wallet flows: the sheet authorises, the confirm
 * page captures). Checked via `instanceof`, like {@see CreatesPaymentIntents},
 * never a required part of {@see PaymentType}. Releasing a hold is
 * {@see SupportsPaymentIntents::voidIntent()}; no separate verb.
 */
interface SupportsPaymentHolds
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
     * Bring an authorised hold to the cart's new payable total. Ok when the
     * hold now covers it (already did, or was incremented); NeedsReauthorization
     * when this hold cannot stretch and the customer must re-authorise.
     */
    public function adjustHold(string $reference, int $amountMinor): HoldAdjustment;

    /**
     * Capture an authorised hold for the final amount (at most the authorised
     * figure). Idempotent per reference. MUST throw when the gateway cannot
     * confirm the capture; an unknown outcome is not a capture.
     */
    public function captureHold(string $reference, int $amountMinor): void;
}
