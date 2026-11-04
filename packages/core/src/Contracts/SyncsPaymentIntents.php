<?php

namespace Lunar\Core\Contracts;

use Lunar\Core\Exceptions\PaymentIntentException;
use Lunar\Core\Models\Cart;

/**
 * Change a live intent's amount as the cart changes.
 *
 * @experimental The shape may change in a minor release until a second driver
 *               implements these capabilities.
 */
interface SyncsPaymentIntents extends CreatesPaymentIntents
{
    /**
     * Acts on the cart's live standard intent; a no-op without one.
     *
     * @throws PaymentIntentException
     */
    public function syncIntent(Cart $cart): void;
}
