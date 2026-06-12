<?php

namespace Lunar\Checkout\Contracts\Actions;

use Lunar\Checkout\Models\CheckoutSession;

interface InvalidatesCheckoutSession
{
    /**
     * Void-first transition to `Cancelled` (spec 0010 §F). When the session
     * carries an in-flight intent the gateway is asked to void it first; an
     * unconfirmable void blocks the terminal transition and returns false
     * (the session stays put for reconciliation). `$reason` is a stable
     * machine code recorded on `meta` and carried by the event.
     */
    public function execute(CheckoutSession $session, string $reason): bool;
}
