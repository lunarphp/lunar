<?php

namespace Lunar\Checkout\Contracts\Actions;

use Lunar\Checkout\Models\CheckoutSession;

interface ReconcilesCheckoutSession
{
    /**
     * Resolve a `PaymentProcessing` session against the gateway's actual
     * intent outcome (spec 0010 §F): captured → complete (or refund on
     * mismatch — no charge survives without an order), voided/failed → back
     * to `Open`, pending → rescheduled without consuming an attempt.
     *
     * `$resolve` is the operator override (`complete` | `refund` | `cancel`)
     * — the sanctioned stall exit. Returns a stable outcome code.
     */
    public function execute(CheckoutSession $session, ?string $resolve = null): string;

    /**
     * Customer-initiated unpin: the client's gateway confirmation step failed
     * or was abandoned, so the customer is stuck on a `PaymentProcessing`
     * session with nothing in flight. Reopens the session for another attempt
     * ONLY after the gateway confirms no money was captured; a captured intent
     * resolves through the normal complete-or-refund path instead, and an
     * unconfirmable outcome stays frozen for the reconciliation sweep.
     *
     * Returns a stable outcome code: `released`, `completed`, `refunded`,
     * `unconfirmed` or `not-applicable`.
     */
    public function release(CheckoutSession $session): string;
}
