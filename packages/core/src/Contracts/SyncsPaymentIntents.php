<?php

namespace Lunar\Core\Contracts;

use Lunar\Core\Models\Cart;

/**
 * Opt-in capability for payment drivers whose pre-created intent carries an
 * amount that can go stale: the intent is created when the payment form
 * mounts, but the cart keeps changing afterwards (shipping selection, address
 * dependent rates, discounts). Checked via `instanceof`, like
 * {@see CreatesPaymentIntents} — never a required part of {@see PaymentType}.
 *
 * A caller syncs immediately before asking the customer to confirm, so the
 * gateway confirms exactly the amount the customer was shown. A driver
 * without the capability is assumed to derive the amount at confirmation
 * time and needs no correction.
 *
 * Unlike {@see SupportsPaymentIntents}, nothing here promises to throw on an
 * unknown outcome. A sync that did not happen leaves the intent at its old
 * amount, which the confirmation step catches, so a driver may fail however
 * it fails.
 */
interface SyncsPaymentIntents
{
    /**
     * Bring the cart's confirmable intent in line with the cart's current
     * payable total. A no-op when the cart has no live intent.
     */
    public function syncIntent(Cart $cart): void;
}
