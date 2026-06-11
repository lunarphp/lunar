<?php

namespace Lunar\Checkout\Contracts\Actions;

use Lunar\Checkout\Models\CheckoutSession;
use Lunar\Core\Models\Cart;

interface CreatesCheckoutSession
{
    /**
     * Create a checkout session from a Lunar cart, pinning the snapshot.
     *
     * @param  array<string, mixed>  $attributes  Caller-supplied extras
     *                                            (client_reference_id, success_url,
     *                                            cancel_url, metadata).
     */
    public function execute(Cart $cart, array $attributes = []): CheckoutSession;
}
