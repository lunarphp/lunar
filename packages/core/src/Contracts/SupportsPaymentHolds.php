<?php

namespace Lunar\Core\Contracts;

use Lunar\Core\DataObjects\PaymentIntentDescriptor;
use Lunar\Core\DataObjects\PriceValue;
use Lunar\Core\Enums\HoldAdjustment;
use Lunar\Core\Exceptions\PaymentIntentException;
use Lunar\Core\Models\Cart;

/**
 * Authorise now, capture later. Only for gateways that confirm cancel and
 * capture synchronously. Release a hold with
 * {@see ReconcilesPaymentIntents::voidIntent()}.
 *
 * @experimental The shape may change in a minor release until a second driver
 *               implements these capabilities.
 */
interface SupportsPaymentHolds extends ReconcilesPaymentIntents
{
    /**
     * Idempotent per cart, including a retry after a lost response.
     *
     * @throws PaymentIntentException
     */
    public function createHold(Cart $cart): PaymentIntentDescriptor;

    /**
     * @throws PaymentIntentException
     */
    public function adjustHold(Cart $cart, string $reference, PriceValue $amount): HoldAdjustment;

    /**
     * @throws PaymentIntentException
     */
    public function captureHold(Cart $cart, string $reference, PriceValue $amount): void;
}
