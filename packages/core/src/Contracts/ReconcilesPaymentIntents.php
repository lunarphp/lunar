<?php

namespace Lunar\Core\Contracts;

use Lunar\Core\DataObjects\PaymentIntentSnapshot;
use Lunar\Core\DataObjects\PaymentRefund;
use Lunar\Core\DataObjects\PriceValue;
use Lunar\Core\Exceptions\PaymentIntentException;
use Lunar\Core\Models\Cart;

/**
 * Report, void and refund an intent before any order exists. Each method's
 * behaviour per status is the lifecycle table in spec 0077. A driver acts only
 * on references it created for the given cart.
 *
 * @experimental The shape may change in a minor release until a second driver
 *               implements these capabilities.
 */
interface ReconcilesPaymentIntents
{
    /**
     * Null when the reference is not one this driver created for the cart.
     *
     * @throws PaymentIntentException
     */
    public function fetchIntent(Cart $cart, string $reference): ?PaymentIntentSnapshot;

    /**
     * @throws PaymentIntentException
     */
    public function voidIntent(Cart $cart, string $reference): void;

    /**
     * `$idempotencyKey` identifies this refund attempt; reuse it on retry.
     *
     * @throws PaymentIntentException
     */
    public function refundIntent(Cart $cart, string $reference, PriceValue $amount, string $idempotencyKey): PaymentRefund;
}
