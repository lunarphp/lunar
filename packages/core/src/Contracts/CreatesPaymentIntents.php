<?php

namespace Lunar\Core\Contracts;

use Lunar\Core\DataObjects\PaymentIntentDescriptor;
use Lunar\Core\Exceptions\PaymentIntentException;
use Lunar\Core\Models\Cart;

/**
 * Pre-create an intent for a cart. Placement is {@see PaymentType::authorize()}
 * with `withData(['reference' => ...])`, idempotent for a placed order.
 *
 * @experimental The shape may change in a minor release until a second driver
 *               implements these capabilities.
 */
interface CreatesPaymentIntents extends ReconcilesPaymentIntents
{
    /**
     * Idempotent per cart, including a retry after a lost response.
     *
     * @throws PaymentIntentException
     */
    public function createIntent(Cart $cart): PaymentIntentDescriptor;
}
