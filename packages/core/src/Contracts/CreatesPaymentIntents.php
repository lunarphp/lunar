<?php

namespace Lunar\Core\Contracts;

use Lunar\Core\DataObjects\PaymentIntentDescriptor;
use Lunar\Core\Exceptions\PaymentIntentException;
use Lunar\Core\Models\Cart;

/**
 * Opt-in capability for payment drivers that pre-create a confirmable object
 * (an intent) for a cart, before any order exists. Checked via `instanceof`,
 * never a required part of {@see PaymentType}. A driver without the
 * capability is confirm-only: it takes payment at order placement and has
 * nothing to pre-create.
 *
 * Implementations MUST be idempotent per cart and per flavour: re-requesting
 * while the cart's standard intent is still confirmable returns it, never a
 * duplicate. That has to survive a lost response, not only a repeat request,
 * so a driver persists an idempotency key for the cart before the first create
 * call and sends it with every attempt. A retry after a timeout then recovers
 * the intent the first attempt made instead of orphaning it.
 *
 * @experimental The shape may change in a minor release until a second driver
 *               implements these capabilities.
 */
interface CreatesPaymentIntents
{
    /**
     * @throws PaymentIntentException when the gateway refuses the call, cannot
     *                                be reached, or returns something the
     *                                driver cannot interpret
     */
    public function createIntent(Cart $cart): PaymentIntentDescriptor;
}
