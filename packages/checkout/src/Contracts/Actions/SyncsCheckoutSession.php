<?php

namespace Lunar\Checkout\Contracts\Actions;

use Lunar\Checkout\DataObjects\CartSnapshot;
use Lunar\Checkout\Models\CheckoutSession;

interface SyncsCheckoutSession
{
    /**
     * Re-pin the session's amounts + fingerprint from a live-cart snapshot
     * while `Open` (spec 0010 §D). A single-statement guarded write: returns
     * false when the freeze won (the session left `Open` mid-flight) and the
     * sync was dropped. Currency/channel divergence invalidates instead.
     */
    public function execute(CheckoutSession $session, CartSnapshot $snapshot): bool;
}
