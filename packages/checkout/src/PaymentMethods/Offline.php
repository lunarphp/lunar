<?php

namespace Lunar\Checkout\PaymentMethods;

/**
 * Payment taken outside the checkout — at a trade counter on collection, on
 * account against agreed terms, by bank transfer against an invoice. Nothing
 * is confirmed by a gateway, so the session completes in place at the pay
 * boundary (spec 0002 §A) and the order is placed unpaid.
 *
 * The only method `lunar/checkout` ships itself: it drives core's own offline
 * payment type and references no gateway, so registering it keeps a store
 * gateway-free. Subclass to relabel it or to gate it on the basket:
 *
 *     class PayOnCollection extends Offline
 *     {
 *         public function handle(): string
 *         {
 *             return 'on-collection';
 *         }
 *
 *         public function isAvailable(Cart $cart): bool
 *         {
 *             return (bool) $cart->getShippingOption()?->collect;
 *         }
 *     }
 */
class Offline extends AbstractPaymentMethod
{
    public function handle(): string
    {
        return 'offline';
    }

    public function label(): string
    {
        return __('lunar-checkout::checkout.payments.offline.label');
    }

    public function driver(): string
    {
        return 'offline';
    }

    public function requiresIntent(): bool
    {
        return false;
    }

    public function component(): string
    {
        return 'offline-notice';
    }
}
