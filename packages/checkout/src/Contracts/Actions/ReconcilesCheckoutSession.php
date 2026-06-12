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
}
