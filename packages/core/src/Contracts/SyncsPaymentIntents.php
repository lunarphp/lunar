<?php

namespace Lunar\Core\Contracts;

use Lunar\Core\Exceptions\PaymentIntentException;
use Lunar\Core\Models\Cart;

/**
 * Opt-in capability for payment drivers whose gateway can change a
 * pre-created intent's amount in place as the cart changes (shipping
 * selection, address dependent rates, discounts). Checked via `instanceof`,
 * never a required part of {@see PaymentType}.
 *
 * A caller MUST NOT let the customer confirm a standard intent until the most
 * recent sync returned cleanly. If the client confirms directly with the
 * gateway, a sync that failed silently means the stale amount is charged
 * before any server-side check runs.
 *
 * A driver without the capability is one whose gateway fixes the amount at
 * creation. Its caller creates the intent once the cart is final, or voids
 * and re-creates it when the total changes.
 *
 * @experimental The shape may change in a minor release until a second driver
 *               implements these capabilities.
 */
interface SyncsPaymentIntents
{
    /**
     * Bring the cart's live standard intent in line with the cart's current
     * payable total. A no-op when the cart has no live standard intent. Never
     * touches a hold: a hold's amount moves through
     * {@see SupportsPaymentHolds::adjustHold()}.
     *
     * @throws PaymentIntentException when the intent could not be brought in
     *                                line, so the caller keeps confirmation
     *                                blocked
     */
    public function syncIntent(Cart $cart): void;
}
