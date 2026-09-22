<?php

namespace Lunar\Core\Contracts;

use Lunar\Core\DataObjects\PaymentIntentDescriptor;
use Lunar\Core\Models\Cart;

/**
 * Opt-in capability for payment drivers that pre-create a confirmable object
 * for a cart, before any order exists (Stripe PaymentIntents, PayPal orders,
 * …). Checked via `instanceof`, like {@see SupportsPaymentIntents} — never a
 * required part of {@see PaymentType}. A driver without the capability is
 * confirm-only: it takes payment at order placement and has nothing to
 * pre-create.
 *
 * Implementations MUST be idempotent per cart: re-requesting while an intent
 * is still confirmable returns the existing intent, never a duplicate.
 */
interface CreatesPaymentIntents
{
    public function createIntent(Cart $cart): PaymentIntentDescriptor;
}
