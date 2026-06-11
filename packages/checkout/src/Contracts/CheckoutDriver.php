<?php

namespace Lunar\Checkout\Contracts;

use Lunar\Checkout\Managers\CheckoutSessionManager;
use Lunar\Checkout\Models\CheckoutSession;

/**
 * The swap seam (spec 0004). Owns the two backend-specific ends of the checkout
 * lifecycle — turning a source cart into a session, and finalising a session
 * into an order. Everything between (the `checkout_sessions` table, the UUID
 * capability token, the state machine, the element model) is backend-neutral
 * and Lunar-owned.
 *
 * Resolved by name from `config('lunar.checkout.driver')` via the
 * {@see CheckoutSessionManager} (standard Manager
 * pattern). Default driver name: `lunar`.
 */
interface CheckoutDriver
{
    /**
     * Ingest an arbitrary source cart into a checkout session — pinning the
     * snapshot (currency, locale, minor-unit totals) and returning the session.
     */
    public function createSession(mixed $source): CheckoutSession;

    /**
     * Finalise a session into an order in whatever system the backend owns,
     * linking it via the session's `order` morph.
     */
    public function complete(CheckoutSession $session): mixed;
}
