<?php

namespace Lunar\Checkout\Contracts\Actions;

use Lunar\Checkout\DataObjects\CartSnapshot;
use Lunar\Checkout\Exceptions\CheckoutSessionConflictException;
use Lunar\Checkout\Models\CheckoutSession;
use Lunar\Core\Models\Cart;

interface CreatesCheckoutSession
{
    /**
     * Create a checkout session from a Lunar cart. `$snapshot` carries the
     * driver-computed amounts + fingerprint pinned at creation. Enforces the
     * one-active-session-per-cart rule (spec 0010 §F.2): supersedes a prior
     * `Open` sibling, refuses while a sibling is `PaymentProcessing`.
     *
     * @param  array<string, mixed>  $attributes  Caller-supplied extras
     *                                            (customer_email, client_reference_id,
     *                                            success_url, cancel_url, metadata).
     *
     * @throws CheckoutSessionConflictException
     */
    public function execute(Cart $cart, CartSnapshot $snapshot, array $attributes = []): CheckoutSession;
}
